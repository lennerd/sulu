<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use JMS\Serializer\SerializationContext;
use PHPCR\PropertyInterface;
use Sulu\Bundle\ContentBundle\Repository\NodeRepository;
use Sulu\Bundle\ContentBundle\Repository\NodeRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Content\Compat\Structure;
use Sulu\Component\Content\Document\Behavior\SecurityBehavior;
use Sulu\Component\Content\Exception\ResourceLocatorNotValidException;
use Sulu\Component\Content\Mapper\ContentMapperRequest;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestController;
use Sulu\Component\Security\Authorization\AccessControl\SecuredObjectControllerInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * handles content nodes.
 */
class NodeController extends RestController
    implements ClassResourceInterface, SecuredControllerInterface, SecuredObjectControllerInterface
{
    use RequestParametersTrait;

    /**
     * returns language code from request.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getLanguage(Request $request)
    {
        return $this->getRequestParameter($request, 'language', true);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(Request $request)
    {
        return $this->getLanguage($request);
    }

    /**
     * returns webspace key from request.
     *
     * @param Request $request
     * @param bool    $force
     *
     * @return string
     */
    private function getWebspace(Request $request, $force = true)
    {
        return $this->getRequestParameter($request, 'webspace', $force);
    }

    /**
     * returns entry point (webspace as node).
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entryAction(Request $request)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);

        $depth = $this->getRequestParameter($request, 'depth', false, 1);
        $ghostContent = $this->getBooleanRequestParameter($request, 'ghost-content', false, false);

        $view = $this->responseGetById(
            null,
            function () use ($language, $webspace, $depth, $ghostContent) {
                try {
                    return $this->getRepository()->getWebspaceNode(
                        $webspace,
                        $language,
                        $depth,
                        $ghostContent
                    );
                } catch (DocumentNotFoundException $ex) {
                    return;
                }
            }
        );

        return $this->handleView($view);
    }

    /**
     * returns a content item with given UUID as JSON String.
     *
     * @param Request $request
     * @param string  $uuid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, $uuid)
    {
        $response = $this->getSingleNode($request, $uuid);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $uuid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getSingleNode(Request $request, $uuid)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);
        $breadcrumb = $this->getBooleanRequestParameter($request, 'breadcrumb', false, false);
        $complete = $this->getBooleanRequestParameter($request, 'complete', false, true);
        $ghostContent = $this->getBooleanRequestParameter($request, 'ghost-content', false, false);

        $view = $this->responseGetById(
            $uuid,
            function ($id) use ($language, $webspace, $breadcrumb, $complete, $ghostContent) {
                try {
                    return $this->getRepository()->getNode(
                        $id,
                        $webspace,
                        $language,
                        $breadcrumb,
                        $complete,
                        $ghostContent
                    );
                } catch (DocumentNotFoundException $ex) {
                    return;
                }
            }
        );

        // preview needs also null value to work correctly
        $view->setSerializationContext(SerializationContext::create()->setSerializeNull(true));

        return $this->handleView($view);
    }

    /**
     * Returns a tree along the given path with the siblings of all nodes on the path.
     * This functionality is required for preloading the content navigation.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $uuid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getTreeForUuid(Request $request, $uuid)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request, false);
        $excludeGhosts = $this->getBooleanRequestParameter($request, 'exclude-ghosts', false, false);
        $excludeShadows = $this->getBooleanRequestParameter($request, 'exclude-shadows', false, false);
        $appendWebspaceNode = $this->getBooleanRequestParameter($request, 'webspace-node', false, false);

        try {
            if ($uuid !== null && $uuid !== '') {
                $result = $this->getRepository()->getNodesTree(
                    $uuid,
                    $webspace,
                    $language,
                    $excludeGhosts,
                    $excludeShadows,
                    $appendWebspaceNode
                );
            } elseif ($webspace !== null) {
                $result = $this->getRepository()->getWebspaceNode($webspace, $language);
            } else {
                $result = $this->getRepository()->getWebspaceNodes($language);
            }
        } catch (DocumentNotFoundException $ex) {
            // TODO return 404 and handle this edge case on client side
            return $this->redirect(
                $this->generateUrl(
                    'get_nodes',
                    [
                        'tree' => 'false',
                        'depth' => 1,
                        'language' => $language,
                        'webspace' => $webspace,
                        'exclude-ghosts' => $excludeGhosts,
                    ]
                )
            );
        }

        return $this->handleView(
            $this->view($result)
        );
    }

    /**
     * Returns nodes by given ids.
     *
     * @param Request $request
     * @param array   $idString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getNodesByIds(Request $request, $idString)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request, false);

        $result = $this->getRepository()->getNodesByIds(
            preg_split('/[,]/', $idString, -1, PREG_SPLIT_NO_EMPTY),
            $webspace,
            $language
        );

        return $this->handleView($this->view($result));
    }

    /**
     * returns a content item for startpage.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);

        $result = $this->getRepository()->getIndexNode($webspace, $language);

        return $this->handleView($this->view($result));
    }

    /**
     * returns all content items as JSON String.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $tree = $this->getBooleanRequestParameter($request, 'tree', false, false);
        $ids = $this->getRequestParameter($request, 'ids');

        if ($tree === true) {
            return $this->getTreeForUuid($request, $this->getRequestParameter($request, 'id', false, null));
        } elseif ($ids !== null) {
            return $this->getNodesByIds($request, $ids);
        }

        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);
        $excludeGhosts = $this->getBooleanRequestParameter($request, 'exclude-ghosts', false, false);

        $parentUuid = $request->get('parent');
        $depth = $request->get('depth', 1);
        $depth = intval($depth);
        $flat = $request->get('flat', 'true');
        $flat = ($flat === 'true');

        // TODO pagination
        $result = $this->getRepository()->getNodes(
            $parentUuid,
            $webspace,
            $language,
            $depth,
            $flat,
            false,
            $excludeGhosts
        );

        return $this->handleView(
            $this->view($result)
        );
    }

    /**
     * Returns the title of the pages for a given smart content configuration.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated will be removed with version 1.2
     */
    public function filterAction(Request $request)
    {
        // load data from request
        $dataSource = $this->getRequestParameter($request, 'dataSource');
        $includeSubFolders = $this->getBooleanRequestParameter($request, 'includeSubFolders', false, false);
        $limitResult = $this->getRequestParameter($request, 'limitResult');
        $tagNames = $this->getRequestParameter($request, 'tags');
        $tagOperator = $this->getRequestParameter($request, 'tagOperator', false, 'or');
        $sortBy = $this->getRequestParameter($request, 'sortBy');
        $sortMethod = $this->getRequestParameter($request, 'sortMethod', false, 'asc');
        $exclude = $this->getRequestParameter($request, 'exclude');
        $webspaceKey = $this->getWebspace($request);
        $languageCode = $this->getLanguage($request);

        // resolve tag names
        $resolvedTags = [];

        /** @var TagManagerInterface $tagManager */
        $tagManager = $this->get('sulu_tag.tag_manager');

        if (isset($tagNames)) {
            $tags = explode(',', $tagNames);
            foreach ($tags as $tag) {
                $resolvedTag = $tagManager->findByName($tag);
                if ($resolvedTag) {
                    $resolvedTags[] = $resolvedTag->getId();
                }
            }
        }

        // get sort columns
        $sortColumns = [];
        if (isset($sortBy)) {
            $columns = explode(',', $sortBy);
            foreach ($columns as $column) {
                if ($column) {
                    $sortColumns[] = $column;
                }
            }
        }

        $filterConfig = [
            'dataSource' => $dataSource,
            'includeSubFolders' => $includeSubFolders,
            'limitResult' => $limitResult,
            'tags' => $resolvedTags,
            'tagOperator' => $tagOperator,
            'sortBy' => $sortColumns,
            'sortMethod' => $sortMethod,
        ];

        /** @var NodeRepository $repository */
        $repository = $this->get('sulu_content.node_repository');
        $content = $repository->getFilteredNodes(
            $filterConfig,
            $languageCode,
            $webspaceKey,
            true,
            true,
            $exclude !== null ? [$exclude] : []
        );

        return $this->handleView($this->view($content));
    }

    /**
     * saves node with given uuid and data.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $uuid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(Request $request, $uuid)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);
        $template = $this->getRequestParameter($request, 'template', true);
        $isShadow = $this->getRequestParameter($request, 'shadowOn', false);
        $shadowBaseLanguage = $this->getRequestParameter($request, 'shadowBaseLanguage', null);

        $state = $this->getRequestParameter($request, 'state');
        $type = $request->query->get('type') ?: 'page';

        if ($state !== null) {
            $state = intval($state);
        }

        $data = $request->request->all();

        $mapperRequest = ContentMapperRequest::create()
            ->setType($type)
            ->setTemplateKey($template)
            ->setWebspaceKey($webspace)
            ->setUserId($this->getUser()->getId())
            ->setState($state)
            ->setIsShadow($isShadow)
            ->setShadowBaseLanguage($shadowBaseLanguage)
            ->setLocale($language)
            ->setUuid($uuid)
            ->setData($data);
        $result = $this->getRepository()->saveNodeRequest($mapperRequest);

        return $this->handleView(
            $this->view($result)
        );
    }

    /**
     * Updates a content item and returns result as JSON String.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        try {
            $language = $this->getLanguage($request);
            $webspace = $this->getWebspace($request);
            $template = $this->getRequestParameter($request, 'template', true);
            $isShadow = $this->getRequestParameter($request, 'isShadow', false);
            $shadowBaseLanguage = $this->getRequestParameter($request, 'shadowBaseLanguage', null);
            $parent = $this->getRequestParameter($request, 'parent');
            $state = $this->getRequestParameter($request, 'state');
            if ($state !== null) {
                $state = intval($state);
            }
            $type = $request->query->get('type', Structure::TYPE_PAGE);

            $data = $request->request->all();

            $mapperRequest = ContentMapperRequest::create()
                ->setType($type)
                ->setTemplateKey($template)
                ->setWebspaceKey($webspace)
                ->setUserId($this->getUser()->getId())
                ->setState($state)
                ->setIsShadow($isShadow)
                ->setShadowBaseLanguage($shadowBaseLanguage)
                ->setLocale($language)
                ->setParentUuid($parent)
                ->setData($data);

            $result = $this->getRepository()->saveNodeRequest($mapperRequest);

            return $this->handleView($this->view($result));
        } catch (ResourceLocatorNotValidException $e) {
            $restException = new RestException('The chosen ResourceLocator is not valid');

            return $this->handleView($this->view($restException->toArray(), 409));
        }
    }

    /**
     * deletes node with given uuid.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $uuid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $uuid)
    {
        $language = $this->getLanguage($request);
        $webspace = $this->getWebspace($request);
        $force = $this->getBooleanRequestParameter($request, 'force', false, false);

        if (!$force) {
            $references = array_filter(
                $this->getRepository()->getReferences($uuid),
                function (PropertyInterface $reference) {
                    return $reference->getParent()->isNodeType('sulu:page');
                }
            );

            if (count($references) > 0) {
                $data = [
                    'structures' => [],
                    'other' => [],
                ];

                foreach ($references as $reference) {
                    $content = $this->get('sulu.content.mapper')->load(
                        $reference->getParent()->getIdentifier(),
                        $webspace,
                        $language,
                        true
                    );
                    $data['structures'][] = $content->toArray();
                }

                return $this->handleView($this->view($data, 409));
            }
        }

        $view = $this->responseDelete(
            $uuid,
            function ($id) use ($webspace) {
                try {
                    $this->getRepository()->deleteNode($id, $webspace);
                } catch (DocumentNotFoundException $ex) {
                    throw new EntityNotFoundException('Content', $id);
                }
            }
        );

        return $this->handleView($view);
    }

    /**
     * trigger a action for given node specified over get-action parameter
     * - move: moves a node
     *   + destination: specifies the destination node
     * - copy: copy a node
     *   + destination: specifies the destination node.
     *
     * @Post("/nodes/{uuid}")
     *
     * @param string  $uuid
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postTriggerAction($uuid, Request $request)
    {
        // extract parameter
        $webspace = $this->getWebspace($request);
        $action = $this->getRequestParameter($request, 'action', true);
        $userId = $this->getUser()->getId();

        // prepare vars
        $repository = $this->getRepository();
        $view = null;
        $data = null;

        try {
            switch ($action) {
                case 'move':
                    $srcLocale = $this->getRequestParameter($request, 'destination', true);
                    $language = $this->getLanguage($request);

                    // call repository method
                    $data = $repository->moveNode($uuid, $srcLocale, $webspace, $language, $userId);
                    break;
                case 'copy':
                    $srcLocale = $this->getRequestParameter($request, 'destination', true);
                    $language = $this->getLanguage($request);

                    // call repository method
                    $data = $repository->copyNode($uuid, $srcLocale, $webspace, $language, $userId);
                    break;
                case 'order':
                    $position = (int) $this->getRequestParameter($request, 'position', true);
                    $language = $this->getLanguage($request);

                    // call repository method
                    $data = $repository->orderAt($uuid, $position, $webspace, $language, $userId);
                    break;
                case 'copy-locale':
                    $srcLocale = $this->getLanguage($request);
                    $destLocale = $this->getRequestParameter($request, 'dest', true);

                    // call repository method
                    $data = $repository->copyLocale($uuid, $userId, $webspace, $srcLocale, explode(',', $destLocale));
                    break;
                default:
                    throw new RestException('Unrecognized action: ' . $action);
            }

            // prepare view
            $view = $this->view($data, $data !== null ? 200 : 204);
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * @return NodeRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->get('sulu_content.node_repository');
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContext()
    {
        $requestAnalyzer = $this->get('sulu_core.webspace.request_analyzer.admin');
        $webspace = $requestAnalyzer->getWebspace();

        if ($webspace) {
            return 'sulu.webspaces.' . $webspace->getKey();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredClass()
    {
        return SecurityBehavior::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredObjectId(Request $request)
    {
        $id = null;

        if (null !== ($uuid = $request->get('uuid'))) {
            $id = $uuid;
        } elseif (null !== ($parent = $request->get('parent')) && $request->getMethod() !== Request::METHOD_GET) {
            // the user is always allowed to get the children of a node
            // so the security check only applies for requests not being GETs
            $id = $parent;
        }

        return $id;
    }
}
