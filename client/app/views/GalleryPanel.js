/**
 *
 * User Interface for the MMS Gallery application.
 *
 */
SampleApps.MMS.App3.views.GalleryPanel = Ext.extend(SampleApps.MMS.App3.views.Base, {

    title   : 'MMS Gallery',
    scroll  : 'vertical',

    /**
     * Calls the buildFeature1 method in order to build the UI for the app.
     */
    buildItems: function() {
        var me = this;

        return [
            me.buildFeature1()
        ];
    },

    /**
     * Builds the UI components for Feature 1: Web gallery of MMS photos sent to short code.
     */
    buildFeature1: function() {
        var me = this,
            cfg = SampleApps.MMS.App3.config;
            images = Ext.getStore('Images'),
            reader = images.proxy.reader;

        return {
            xtype   : 'form',
            itemId  : 'feature1',
            border  : '5 0 10 0',
            items   : [
                {
                    xtype    : 'fieldset',
                    title    : 'Unicorn & Bacon Web Gallery (MMS photos)',
                    defaults : {
                        labelWidth : '40%'
                    },
                    items : [
                        {
                            xtype   : 'container',
                            styleHtmlContent : true,
                            html    : '<p># of Photos Submitted (' + cfg.shortCode + '): ' + reader.jsonData[reader.totalProperty] + '</p>'
                        },
                        {
                            xtype            : 'list',
                            scroll           : false, // scrolling handled by the form
                            disableSelection : true,
                            itemTpl          : me.buildTpl(),
                            store            : images
                        }
                    ]
                }
            ]
        };
    },

    /**
     * Builds a custom Ext.XTemplate to show the gallery data.
     *
     */
    buildTpl: function() {
        var me = this,
            cfg = SampleApps.MMS.App3.config;

        return new Ext.XTemplate(
            '<div onClick="share();">',
            '  <img src="' + cfg.galleryImagesFolder + '{path}"/>',
            '</div>',
            '<div><b>Sent from: </b>{senderAddress}</div>',
            '<div><b>On: </b>{date}</div>',
            '<div>{text}</div>',
            ''
        );
    }

});

Ext.reg('att-mms-gallery', SampleApps.MMS.App3.views.GalleryPanel);