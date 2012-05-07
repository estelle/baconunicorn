/**
 * Controller for SampleApps.MMS.App3.views.InfoPanel
 *
 * @class SampleApps.MMS.App3.controllers.Info
 *
 * @extends Ext.Controller
 */
Ext.regController('Info', {

    /**
     * Returns to the Info Panel home page (#index)
     */
    openInfo: function() {
        Ext.dispatch({
            controller : 'Info',
            action     : 'index',
            historyUrl : 'Info/index'
        });
    },

    // private
    index: function() {
        var me = this,
            app = me.application,
            viewport = app.viewport,
            newCard;

        newCard = viewport.add({
            xtype : 'att-infopanel',
            listeners   : {
                scope       : me,
                afterrender : me.onAfterRender
            }
        });
        viewport.setActiveItem(newCard, { type : 'slide', direction : 'up' });
    },

    /**
     * Called after the Info Panel has rendered and sets up the tap listener on the 'Done' button
     */
    onAfterRender: function(pnl) {
        var me = this,
            btnDone = pnl.down('toolbar button[itemId=btnDone]');

        btnDone.on('tap', me.onDoneBtnTap, me);
    },

    /**
     * Method called when the user taps on the 'Done' button.
     * It first checks to see if a previous item exists and if so, makes this item the active item.
     * If it cannot find a previous item, this method dispatches to the App controllers 'openHome' method
     */
    onDoneBtnTap: function(btn) {
        var me = this,
            viewport = btn.up('#viewport'),
            layout = viewport.getLayout(),
            prev;

        prev = layout.getPrev();
        if (prev) {
            viewport.setActiveItem(prev);
        } else {
            Ext.dispatch({
                controller  : 'App',
                action      : 'openHome'
            });
        }

    }

});