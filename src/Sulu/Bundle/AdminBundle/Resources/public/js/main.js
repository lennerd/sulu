/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require(['husky'], function (Husky) {

    'use strict';

    var app = new Husky({debug: { enable: true }});


    require(['text!/admin/bundles'], function (text) {
        var bundles = JSON.parse(text);

        bundles.forEach(function (bundle) {
            app.use('/bundles/' + bundle + '/js/main.js');
        }.bind(this));

        app.use('aura_extensions/backbone-relational');

        app.components.addSource('suluadmin', '/bundles/suluadmin/js/components');

        app.start();
    });

});
