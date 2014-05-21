define(function(){"use strict";var a={activeTab:null,data:{},instanceName:"collection"},b={dropzoneSelector:".dropzone-container",toolbarSelector:".list-toolbar-container",datagridSelector:".datagrid-container"};return{view:!0,templates:["/admin/media/template/media/collection"],initialize:function(){this.options=this.sandbox.util.extend(!0,{},a,this.options),this.bindCustomEvents(),this.render()},bindCustomEvents:function(){this.sandbox.on("sulu.list-toolbar.change.thumbnail-small",function(){this.sandbox.emit("husky.datagrid.view.change","thumbnail",{large:!1})}.bind(this)),this.sandbox.on("sulu.list-toolbar.change.thumbnail-large",function(){this.sandbox.emit("husky.datagrid.view.change","thumbnail",{large:!0})}.bind(this)),this.sandbox.on("sulu.list-toolbar.change.table",function(){this.sandbox.emit("husky.datagrid.view.change","table")}.bind(this)),this.sandbox.on("sulu.header.back",function(){this.sandbox.emit("sulu.media.collections.list")}.bind(this)),this.sandbox.on("husky.dropzone."+this.options.instanceName+".files-added",function(a){this.addFilesToDatagrid(a)}.bind(this))},render:function(){this.sandbox.dom.html(this.$el,this.renderTemplate("/admin/media/template/media/collection")),this.setHeaderInfos(),this.startDropzone(),this.startDatagrid()},setHeaderInfos:function(){this.sandbox.emit("sulu.header.set-title",this.options.data.title),this.sandbox.emit("sulu.header.set-breadcrumb",[{title:"navigation.media"},{title:"media.collections.title",event:"sulu.media.collections.list"},{title:this.options.data.title}]),this.sandbox.emit("sulu.header.set-title-color",this.options.data.style.color)},startDropzone:function(){this.sandbox.start([{name:"dropzone@husky",options:{el:this.$find(b.dropzoneSelector),url:"/admin/api/media?collection%5Bid%5D="+this.options.data.id,method:"POST",paramName:"fileVersion",instanceName:this.options.instanceName}}])},startDatagrid:function(){this.sandbox.sulu.initListToolbarAndList.call(this,"accountsFields","/admin/api/accounts/fields",{el:this.$find(b.toolbarSelector),instanceName:this.options.instanceName,parentTemplate:"default",template:"changeable",inHeader:!0},{el:this.$find(b.datagridSelector),url:"/admin/api/accounts?flat=true",view:"thumbnail",pagination:!1})},addFilesToDatagrid:function(a){for(var b=-1,c=a.length;++b<c;)a[b].selected=!0;this.sandbox.emit("husky.datagrid.records.add",a)}}});