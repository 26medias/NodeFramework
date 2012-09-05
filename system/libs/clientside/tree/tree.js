/**
	photofeed
	@version:		1.0.0
	@author:		Julien Loutre <julien.loutre@gmail.com>
*/
(function($){
 	$.fn.extend({
 		tree: function() {
			var plugin_namespace = "tree";
			
			var pluginClass = function() {};
			
			pluginClass.prototype.init = function (options) {
				try {
					
					var scope = this;
					
					this.options = $.extend({
						
					},options);
					
					scope.element.addClass("jstree");
					
					scope.loadDir(scope.element, scope.options.root);
					
					scope.element.on('click', '.treeNode', function() {
						if ($(this).parent().hasClass("opened")) {
							$(this).parent().find("ul").first().slideUp();
							$(this).parent().removeClass("opened");
							$(this).parent().addClass("closed");
						} else if ($(this).parent().hasClass("closed")) {
							$(this).parent().find("ul").first().slideDown();
							$(this).parent().removeClass("closed");
							$(this).parent().addClass("opened");
						} else {
							$(this).parent().addClass("opened");
							scope.loadDir($(this).parent(), $(this).attr('data-path'));
						}
					});
					scope.element.on('click', '.checkbox', function() {
						if ($(this).hasClass("checked")) {
							$(this).removeClass("checked");
							if ($(this).hasClass("treeDirLeaf")) {
								$(this).parent().find(".checkbox").removeClass("checked");
							}
						} else {
							$(this).addClass("checked");
							// check all, remove partials in children
							if ($(this).hasClass("partial")) {
								$(this).parent().find(".checkbox").removeClass("partial");
							}
							if ($(this).hasClass("treeDirLeaf")) {
								$(this).parent().find(".checkbox").addClass("checked");
							}
						}
						// partial checkboxes
						var parentNodes = $(this).parents("li");
						var i = 0;
						if (parentNodes.length > 0) {
							for (i=1;i<parentNodes.length;i++) {
								$(parentNodes[i]).find(".checkbox").first().addClass("partial").removeClass("checked");
							}
						}
					});
					
				} catch (err) {
					this.error(err);
				}
			};
			pluginClass.prototype.loadDir = function (node, dir) {
				try {
					
					var scope 	= this;
					$.ajax({
						url: 		scope.options.srv,
						dataType:	"json",
						type:		"POST",
						data:		{
							dir:	dir
						},
						success: 	function(data){
							var i;
							if (data.dirs.length > 0 || data.files.length > 0) {
								var ul = $.create('ul', node).slideUp();
								for (i=0;i<data.dirs.length;i++) {
									scope.createDir(ul, data.path, data.dirs[i]);
								}
								for (i=0;i<data.files.length;i++) {
									scope.createFile(ul, data.path, data.files[i]);
								}
								ul.slideDown();
							}
						}
					});
					
				} catch (err) {
					this.error(err);
				}
			};
			pluginClass.prototype.createDir = function (node, path, data) {
				try {
					
					var scope 	= this;
					var li		= $.create("li", node);
					li.attr("data-path", path+"/"+data);
					li.addClass("treeDir");
					
					var span	= $.create("span", li);
					span.attr("data-path", path+"/"+data);
					span.addClass("treeNode");
					
					var chk		= $.create("span", li);
					chk.attr("data-path", path+"/"+data);
					chk.addClass("checkbox");
					chk.addClass("treeDirLeaf");
					chk.html(data);
					
					if (node.parent().find(".checkbox").hasClass("checked")) {
						chk.addClass("checked");
					}
					
				} catch (err) {
					this.error(err);
				}
			};
			pluginClass.prototype.createFile = function (node, path, data) {
				try {
					
					var scope 	= this;
					var li		= $.create("li", node);
					li.attr("data-path", path);
					li.addClass("treeFile");
					
					var chk		= $.create("span", li);
					chk.attr("data-path", path+"/"+data);
					chk.addClass("checkbox");
					chk.addClass("treeFileLeaf");
					chk.html(data);
					
					if (node.parent().find(".checkbox").hasClass("checked")) {
						chk.addClass("checked");
					}
					
				} catch (err) {
					this.error(err);
				}
			};
			pluginClass.prototype.export = function (options) {
				try {
					
					var scope 	= this;
					
					var raw = scope.element.find(".checked");
					var export_files 	= [];
					var export_dirs 	= [];
					var i = 0;
					for (i=0;i<raw.length;i++) {
						var node = $(raw[i]);
						//
						if (node.hasClass('treeFileLeaf')) {
							var parentNode = node.closest(".treeDir");
							console.log(node, parentNode, parentNode.find('.treeDirLeaf').first().hasClass('checked'));
							if (!parentNode.find('.treeDirLeaf').first().hasClass('checked')) {
								export_files.push(node.attr('data-path'));
							}
						} else {
							var parentNode = node.closest(".treeDir").parent().closest(".treeDir");
							console.log(node, parentNode, parentNode.find('.treeDirLeaf').first().hasClass('checked'));
							if (!parentNode.find('.treeDirLeaf').first().hasClass('checked')) {
								export_dirs.push(node.attr('data-path'));
							}
						}
					}
					
					options.callback({
						files:	export_files,
						dirs:	export_dirs
					});
					
				} catch (err) {
					this.error(err);
				}
			};
			
			
			
			
			
			pluginClass.prototype.__init = function (element) {
				try {
					this.element = element;
				} catch (err) {
					this.error(err);
				}
			};
			// centralized error handler
			pluginClass.prototype.error = function (e) {
				if (console && console.info) {
					console.info("error on "+plugin_namespace+":",e);
				}
			};
			// Centralized routing function
			pluginClass.prototype.execute = function (fn, options) {
				try {
					if (typeof(this[fn]) == "function") {
						var output = this[fn].apply(this, [options]);
					} else {
						this.error("'"+fn.toString()+"()' is not a function");
					}
				} catch (err) {
					this.error(err);
				}
			};
			
			// process
			var fn;
			var options;
			if (arguments.length == 0) {
				fn = "init";
				options = {};
			} else if (arguments.length == 1 && typeof(arguments[0]) == 'object') {
				fn = "init";
				options = $.extend({},arguments[0]);
			} else {
				fn = arguments[0];
				options = arguments[1];
			}
			$.each(this, function(idx, item) {
				// if the plugin does not yet exist, let's create it.
				if ($(item).data(plugin_namespace) == null) {
					$(item).data(plugin_namespace, new pluginClass());
					$(item).data(plugin_namespace).__init($(item));
				}
				$(item).data(plugin_namespace).execute(fn, options);
			});
			return this;
    	}
	});
	
})(jQuery);

