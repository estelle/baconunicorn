/**
 *
 * User Interface for the Information Panel.
 *
 */
SampleApps.MMS.App3.views.InfoPanel = Ext.extend(Ext.Panel, {

    title   : 'Info',
    scroll  : 'vertical',
    itemId  : 'infoPanel',

    /**
     * Initializes the component by setting the dockedItems, items and layout
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            dockedItems : me.buildDocks(),
            items       : me.buildItems()
        });

        SampleApps.MMS.App3.views.InfoPanel.superclass.initComponent.call(me);
    },

    /**
     * Builds the top toolbar for the Info Panel
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
                        text    : 'Done',
                        itemId  : 'btnDone',
                        ui      : 'light'
                    },
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
     * Builds the items (UI Components)
     * In the case of the Info Panel, the only UI Componet is just a container displaying the copyright text.
     */
    buildItems: function() {
        var me = this;

        return {
            styleHtmlContent : true,
            html :  "<p>&copy; 2011 AT&T Intellectual Property. All rights reserved. <a href='http://developer.att.com'>http://developer.att.com</a></p>"+
                    "<p>The Application hosted on this site are working examples intended to be used for reference in creating products to consume AT&T Services and not meant to be used as part of your product. The data in these pages is for test purposes only and intended only for use as a reference in how the services perform.</p>" +
                    "<p>For download of tools and documentation, please go to <a href='https://devconnect-api.att.com'>https://devconnect-api.att.com</a></p>" +
                    "<p>For more information contact developer.support@att.com</p>"
        };
    }


});

Ext.reg('att-infopanel', SampleApps.MMS.App3.views.InfoPanel);