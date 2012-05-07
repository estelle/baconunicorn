/**
 * Controller for SampleApps.MMS.App3.views.Viewport.
 *
 * @class SampleApps.MMS.App3.controllers.Viewport
 *
 * @extends Ext.Controller
 */
Ext.regController('Viewport', {

    /**
     * Creates the viewport which is used to render all pages of the app.
     * Sets up a listener for the afterrender event so further processing can be done.
     */
    createViewport: function() {
        var me = this,
            app = me.application,
            viewport = app.viewport;

        if (!viewport) {
            viewport = app.viewport = new SampleApps.MMS.App3.views.Viewport({
                listeners : {
                    scope       : me,
                    afterrender : me.onAfterRender
                }
            });
        }

    },

    /**
     * Sets up a listener for the tap event on all toolbar buttons.
     */
    onAfterRender: function(pnl) {
        var me = this,
            actions = pnl.query('toolbar button');

        for (var i=0; len=actions.length, i<len; i++) {
            actions[i].on('tap', me.onActionClick, me);
        }
    },

    /**
     * Method that handles the tap event for the Home, Prefs, and Info toolbar buttons.
     */
    onActionClick: function(btn) {

        switch(btn.itemId) {

            case 'btnHome' :
                Ext.dispatch({
                    controller : 'Main',
                    action     : 'openHome'
                });
                break;

            case 'btnPrefs' :
                Ext.dispatch({
                    controller : 'Prefs',
                    action     : 'openPrefs'
                });
                break;

            case 'btnInfo'  :
                Ext.dispatch({
                    controller : 'Info',
                    action     : 'openInfo'
                });
                break;

            default :
                Ext.dispatch({
                    controller : 'App',
                    action     : 'openHome'
                });
        }
    }

});