/**
 *
 * User Interface for the Preferences Panel.
 *
 */
SampleApps.MMS.App3.views.PrefsPanel = Ext.extend(Ext.Panel, {

    title   : 'Preferences',
    scroll  : 'vertical',
    itemId  : 'prefsPanel',

    /**
     * Initializes the component by setting the dockedItems, items and layout
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            dockedItems : me.buildDocks(),
            items       : me.buildItems()
        });

        SampleApps.MMS.App3.views.PrefsPanel.superclass.initComponent.call(me);
    },

    /**
     * Builds the top toolbar for the Prefs Panel
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
     * Builds the items (UI Components) for the Prefs Panel
     */
    buildItems: function() {
        var me = this;

        return {
            xtype   : 'form',
            items   : [
                {
                    xtype    : 'fieldset',
                    title    : 'Application Preferences',
                    defaults : {
                        labelWidth : '40%'
                    },
                    items : [
                        {
                            xtype    : 'textfield',
                            label    : 'Phone',
                            name     : 'address',
                            required : true
                        },
                        {
                            xtype    : 'fieldset',
                            title    : 'Theme',
                            defaults : {
                                xtype : 'radiofield',
                                labelWidth : '80%'
                            },
                            items   : [
                                {
                                    name    : 'theme',
                                    value   : 'att-blue',
                                    label   : 'Blue'
                                },
                                {
                                    name    : 'theme',
                                    value   : 'att-orange',
                                    label   : 'Orange'
                                },
                                {
                                    name    : 'theme',
                                    value   : 'att-orange-blue',
                                    label   : 'Orange w/blue toolbars'
                                },
                                {
                                    name    : 'theme',
                                    value   : 'att-blue-orange',
                                    label   : 'Blue w/orange toolbars'
                                }
                            ]
                        }
                    ]
                },
                {
                    xtype   : 'button',
                    itemId  : 'btnSave',
                    ui      : 'action',
                    text    : 'Save Preferences'
                }
            ]
        };
    }


});

Ext.reg('att-prefspanel', SampleApps.MMS.App3.views.PrefsPanel);