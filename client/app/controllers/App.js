/**
 * Controller used by all sample apps when packaged individually
 *
 * @class SampleApps.MMS.App3.controllers.App
 *
 * @extends Ext.Controller
 */
 Ext.regController('App', {

    /**
     * Each application's controller 'index' method dispatches to this method.
     * Used with the apps are packaged individually
     */
    openAppStandAlone: function(opts) {
        var me = this,
            app = me.application,
            viewport = app.viewport,
            comp;

        comp = viewport.add({
            xtype : opts.xtype,
            listeners   : {
                scope       : me,
                afterrender : me.onAppAfterRender
            }
        });

        viewport.setActiveItem(comp);
    },

    /**
     * Used to improve performance.  Simply call this method
     * passing the parent and the item to be removed so
     * that we can keep the dom lean and mean :)
     */
    removeComponent: function(opts) {
        opts.parent.remove(opts.item);
    },

    /**
     * Called after the afterrender event has fired for an app and it calls the handleAppAfterRender method in the Main controller.
     */
    onAppAfterRender: function(pnl) {
        var me = this,
            mainCtl = Ext.ControllerManager.get('Main');

        mainCtl.handleAppAfterRender(pnl);

    }

});