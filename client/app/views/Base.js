/**
 *
 * SampleApps.MMS.App3.views.Base class is an Abstract Base class used by other panels so that common methods can be inherited.
 *
 */
SampleApps.MMS.App3.views.Base = Ext.extend(Ext.Panel, {

    /**
     * Initialize the component with dockedItems, items, and a layout
     * Classes that extend this class can either use this method or override it.
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            dockedItems : me.buildDocks(),
            items       : me.buildItems()
        });

        SampleApps.MMS.App3.views.Base.superclass.initComponent.call(me);
    },

    /**
     * Builds a top toolbar that is used for the panel's dockedItems property.
     * This toolbar will include a title and the AT&T logo.
     * Classes that extend this class can either use this method or override it.
     */
    buildDocks : function() {
        var me = this;

        return [
            {
                xtype : 'toolbar',
                title : me.title,
                dock  : 'top',
                ui    : 'att-toolbar',
                items : [
                    {
                        xtype : 'spacer'
                    },
                    {
                        cls   : 'icon-att',
                        width : 70,
                        ui    : 'plain'
                    }
                ]
            }
        ];
    },

    /**
     * Abstract method to be overridden by subclasses
     */
    buildItems: function() {
        return [];
    }

});

Ext.reg('att-base', SampleApps.MMS.App3.views.Base);