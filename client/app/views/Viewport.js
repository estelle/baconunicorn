// go ahead and register the 'views' namespaces in Viewport.js so that these namespaces
// are available once their corresponding js files are loaded
Ext.ns(
    'SampleApps.MMS.App3.views.device',
    'SampleApps.MMS.App3.views.mms',
    'SampleApps.MMS.App3.views.payment',
    'SampleApps.MMS.App3.views.sms',
    'SampleApps.MMS.App3.views.wap'
);
/**
 *
 * Main Viewport of the application where each 'app' panel is swapped in and out.
 *
 */
SampleApps.MMS.App3.views.Viewport = Ext.extend(Ext.Panel, {

    id         : 'viewport',
    fullscreen : true,
    layout     : 'card',

    /**
     * Initializes the component by setting the dockedItems.
     * Note that the items config is not set since items are added dynamically.
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            //items : me.buildItems(),
            dockedItems : me.buildDocks()
        });


        SampleApps.MMS.App3.views.Viewport.superclass.initComponent.call(me);

        me.on('cardswitch', me.announceCardSwitch, me);
    },

    /**
     * Makes sure that the cardswitch event is unregistered
     */
    beforeDestroy: function() {
        var me = this;

        me.un('cardswitch', me.announceCardSwitch, me);

        SampleApps.MMS.App3.views.Viewport.superclass.beforeDestroy.call(me);
    },


    /**
     * Builds the App List for when the applications are viewed as a 'combined' application
     */
    buildItems: function() {
        return [{
            xtype : 'att-applist'
        }]
    },

    /**
     * Builds the bottom toolbar that includes the Home, Prefs and Info buttons
     */
    buildDocks: function() {
        return [
            {
                xtype   : 'toolbar',
                dock    : 'bottom',
                ui      : 'att-toolbar',
                layout  : {
                    pack : 'center'
                },
                defaults: {
                    iconMask : true,
                    ui       : 'plain'
                },
                items   : [
                    {
                        iconCls : 'home',
                        itemId  : 'btnHome'
                    },
                    {
                        iconCls : 'settings',
                        itemId  : 'btnPrefs'
                    },
                    {
                        iconCls : 'info',
                        itemId  : 'btnInfo'
                    }
                ]
            }
        ];
    },

    /**
     * we do this to improve the performance of the app since
     * on a mobile device the performance is directly related
     * to how big our dom is.  And since this app could have
     * more sample apps added (and thus more panels for the card
     * layout), we'll just make sure only one panel in the card
     * is in the dom at a time
     */
    announceCardSwitch: function(viewport, newItem, oldItem) {

        // if we are going to the infoPanel or prefsPanel then leave the oldItem
        if (newItem.itemId === 'infoPanel' || newItem.itemId === 'prefsPanel') {
            return;
        }

        // we'll keep the appList since that's the main entry point
        if (oldItem && oldItem.itemId !== 'appList') {
            Ext.dispatch({
                controller : 'App',
                action     : 'removeComponent',

                parent     : viewport,
                item       : oldItem
            });
        }
    }
});

Ext.reg('att-viewport', SampleApps.MMS.App3.views.Viewport);