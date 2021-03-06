define(["config","text!sulusearch/components/search-results/main.html","text!sulusearch/components/search-results/search-results.html"],function(a,b,c){"use strict";var d={instanceName:"",searchUrl:"/admin/search",enabledCategoriesUrl:"/admin/search/categories",pageLimit:100,displayLogo:!1},e=function(a){return"sulu.search-results."+(this.options.instanceName?this.options.instanceName+".":"")+a},f=function(){return e.call(this,"initialized")};return{initialize:function(){this.options=this.sandbox.util.extend(!0,{},d,this.options),this.mainTemplate=this.sandbox.util.template(b),this.searchResultsTemplate=this.sandbox.util.template(c),this.enabledCategories=[],this.categories={},this.categoriesStore={},this.totals={},this.state={page:1,pages:1,loading:!1,hasNextPage:!0,category:"all",query:""},this.loadCategories().then(function(){this.render(),this.startInfiniteScroll(),this.bindEvents(),this.bindDomEvents(),this.displayLogo(),this.sandbox.emit(f.call(this))}.bind(this))},bindEvents:function(){this.sandbox.on("sulu.data-overlay.show",this.focusInput.bind(this)),this.sandbox.on("sulu.dropdown-input."+this.dropDownInputInstance+".action",this.dropDownInputActionHandler.bind(this)),this.sandbox.on("sulu.dropdown-input."+this.dropDownInputInstance+".clear",this.dropDownInputClearHandler.bind(this)),this.sandbox.on("sulu.dropdown-input."+this.dropDownInputInstance+".change",this.dropDownInputActionHandler.bind(this))},bindDomEvents:function(){this.$el.on("click",".search-results-section li",this.openSearchEntry.bind(this))},render:function(){var a=this.mainTemplate({translate:this.sandbox.translate});this.$el.html(a),this.createSearchInput(),this.createSearchTotals()},displayLogo:function(){if(!0===this.options.displayLogo){var a=this.$el.find(".logo").first();a.removeClass("hidden"),this.sandbox.util.delay(function(){a.addClass("is-visible")},10)}},hideLogo:function(){var a=this.$el.find(".logo").first();a.addClass("hidden"),a.removeClass("is-visible")},focusInput:function(){this.sandbox.emit("sulu.dropdown-input."+this.dropDownInputInstance+".focus")},addCategory:function(a){var b={id:a,name:this.sandbox.translate("search-overlay.category."+a+".title"),placeholder:this.sandbox.translate("search-overlay.placeholder."+a)};this.categories[a]=b,this.enabledCategories.push(b)},startInfiniteScroll:function(){var a=this.$el.find(".iscroll");this.sandbox.infiniteScroll.initialize(a,this.loadNextPage.bind(this),50)},loadCategories:function(){return this.sandbox.util.load(this.options.enabledCategoriesUrl).then(function(a){this.addCategory("all"),a.forEach(this.addCategory.bind(this))}.bind(this))},createSearchInput:function(){this.dropDownInputInstance="searchResults",this.sandbox.start([{name:"dropdown-input@sulusearch",options:{el:this.$el.find(".search-results-bar"),instanceName:this.dropDownInputInstance,preSelectedElement:"all",data:this.enabledCategories,focused:!0}}])},createSearchTotals:function(){this.searchTotalsInstanceName="searchTotals",this.sandbox.start([{name:"search-totals@sulusearch",options:{el:this.$el.find(".search-totals"),instanceName:this.searchTotalsInstanceName,categories:this.categories}}])},load:function(){var a={q:this.state.query,page:this.state.page,limit:this.options.pageLimit},b=this.options.searchUrl+"/query?"+$.param(a),c=this.state.category;return c&&"all"!==c&&(b+="&category="+c),this.sandbox.util.load(b).then(this.parse.bind(this))},loadNextPage:function(){var a=this.sandbox.data.deferred();return this.state.hasNextPage&&!this.state.loading?(this.startLoader(),this.state.page++,this.load().then(this.mergeResults.bind(this)).then(this.updateResults.bind(this)).then(this.updateTotals.bind(this))):(a.resolve(),a)},parse:function(b){var c,d,e=b._embedded.result||[],f={};return this.state.page=b.page,this.state.pages=b.pages,this.state.loading=!1,this.state.hasNextPage=b.page<b.pages,this.totals=b.totals,e.forEach(function(b){var e;c=b.document.category,d=this.getEntryDeepUrl(c,b.document),b.document.deepUrl=d,e=a.get("sulusearch."+c+".options")||{},f[c]?f[c].results.push(b.document):f[c]={category:c,results:[b.document],options:this.sandbox.util.extend(!0,{},{image:!0},e)}}.bind(this)),f},mergeResults:function(a){return a&&Object.keys(a).forEach(function(b){this.categoriesStore[b]?this.categoriesStore[b].results=this.categoriesStore[b].results.concat(a[b].results):this.categoriesStore[b]=a[b]}.bind(this)),this.categoriesStore},dropDownInputActionHandler:function(a){a.value&&(this.state.query=a.value,this.state.category=a.selectedElement,this.state.page=1,this.categoriesStore={},this.hideLogo(),this.startLoader(),this.updateResults(),this.load().then(function(a){this.categoriesStore=a,this.updateResults(a),this.updateTotals(a)}.bind(this)))},dropDownInputClearHandler:function(){this.updateResults(),this.totals={},this.updateTotals()},getEntryDeepUrl:function(a,b){var c=this.sandbox.urlManager.getUrl(a,b);return c},categoryIconMapping:{contact:"fa-user",page:"fa-file-o",snippet:"fa-file",account:"fa-university"},getTemplate:function(a){var b=null;return a&&(b=[],Object.keys(a).forEach(function(c){b.push(a[c])}.bind(this))),this.searchResultsTemplate({sections:b,categories:this.categories,totals:this.totals,categoryIconMapping:this.categoryIconMapping,translate:this.sandbox.translate})},updateResults:function(a){var b=this.getTemplate(a);this.stopLoader(),this.$el.find(".search-results").html(b)},updateTotals:function(a){this.sandbox.emit("sulu.search-totals."+this.searchTotalsInstanceName+".update",this.totals,this.state.category)},openSearchEntry:function(a){var b=$(a.currentTarget),c=b.data("url");c&&(this.sandbox.emit("sulu.router.navigate",c),this.sandbox.emit("sulu.data-overlay.hide"))},startLoader:function(){var a=this.sandbox.dom.createElement('<div class="search-results-loader"/>');this.sandbox.dom.append(this.$el.find(".search-results-loader-container"),a),this.sandbox.start([{name:"loader@husky",options:{el:a,size:"100px",color:"#ccc"}}])},stopLoader:function(){this.sandbox.stop(".search-results-loader")}}});