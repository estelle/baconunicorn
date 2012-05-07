Ext.Router.draw(function(map) {
    map.connect(':controller/:action');
    map.connect('index',  {controller: 'Gallery', action: 'index'});
});