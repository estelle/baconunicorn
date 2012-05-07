/**
 * Main controller used by all sample apps.
 *
 * @class SampleApps.MMS.App3.controllers.Main
 *
 * @extends Ext.Controller
 */
Ext.regController('Main', {

    /**
     * Returns to the main #index (home) page
     */
    openHome: function() {
        location.href = 'index.html';
    },

    /**
     * Creates the applications main Viewport, loads in any saved preferences and defines the overlays used to display messages and and google map that the TL application uses.
     */
    launchApp: function() {
        var me = this,
            app = me.application,
            localStore = Ext.StoreMgr.get('Preferences'),
            cfg = SampleApps.MMS.App3.config,
            record = localStore ? localStore.getAt(0) : null;

        // make sure this is a supported browser
        if ((/(WebKit)/i).test(navigator.userAgent) == false) {
            alert('Sencha Touch does not support this browser.  Please use a WebKit based browser.');
            return;
        }

        // check to see if private browsing in iOS is turned on
        try {
            sessionStorage.setItem('test','test');
            sessionStorage.removeItem('test');
        } catch(e) {
            alert('This application requires HTML5 Web Storage. We have detected that web storage is not working in your browser. The usual cause to this is when you enable Private Browsing. Please disable this and try again.');
            return;
        }
        // create the view port
        Ext.dispatch({
            controller  : 'Viewport',
            action      : 'createViewport'
        });

        // initialize the Provider component
        app.provider = new Att.Provider({
            apiBasePath : cfg.apiBasePath
        });

        app.prefs = (record) ? record.data : {};
        if (app.prefs.theme && app.prefs.theme !== 'att-blue') {
            Ext.dispatch({
                controller  : 'Prefs',
                action      : 'switchTheme',
                theme       : app.prefs.theme
            });
        }

    },

    /**
     * Called after the afterrender event has fired for an app and it checks the xtype of the panel
     * to see if any further controller/actions need to be dispatched.  Also loads the previous results if needed.
     */
    handleAppAfterRender: function(pnl) {
        var me = this;

        // handle any extra processing for various xtypes here
        switch(pnl.xtype) {

            case 'att-sms-voting' :
                Ext.dispatch({
                    controller  : 'sms.Voting',
                    action      : 'setupVotesStore',
                    frm         : pnl.down('#feature1')
                });
                break;

            case 'att-mms-basic' :
                Ext.dispatch({
                    controller  : 'mms.Basic',
                    action      : 'setUpFileSelectListeners',
                    panel       : pnl
                });
                break;

            case 'att-mms-coupon' :
                Ext.dispatch({
                    controller  : 'mms.Coupon',
                    action      : 'getSubjectData',
                    panel       : pnl
                });
                Ext.dispatch({
                    controller  : 'mms.Coupon',
                    action      : 'getPhoneData',
                    panel       : pnl
                });
                break;

            case 'att-payment-notary' :
                Ext.dispatch({
                    controller  : 'payment.Notary',
                    action      : 'loadData',
                    panel       : pnl
                });
                break;

            case 'att-payment-singlepay' :
                Ext.dispatch({
                    controller  : 'payment.SinglePay',
                    action      : 'loadData',
                    panel       : pnl
                });
                break;

            case 'att-payment-subscription' :
                Ext.dispatch({
                    controller  : 'payment.Subscription',
                    action      : 'loadData',
                    panel       : pnl
                });
                break;

            default :
                break;
        }


    },



    /**
     * Shows a panel to display the results from a call to one of the Att.Provider API's..
     */
    showResults: function(options) {
        var me = this,
            frm = Ext.ComponentQuery.query('#' + options.resultsFormId)[0],
            pnl = frm.up('panel'),
            results = options.results,
            resultType = options.resultType || 'success';


        // remove any previous results (just in case)
        me.removePreviousResults();

        // make sure we have a stringified version of the results
        results = (Ext.isObject(results)) ? JSON.stringify(results, null, '\t') : results;

        // add a container to the form to show the results
        frm.add({
            itemId      : 'results',
            xtype       : 'panel',
            cls         : resultType + '-panel',
            padding     : 4,
            margin      : '4 0 0 0',
            dockedItems : new Ext.Toolbar({ dock : 'top', title: resultType.toUpperCase()}),
            items       : {
                xtype : 'container',
                html  : results
            }
        });

        // force a doLayout so we can see the changes
        pnl.doLayout();

        // hide any masks
        pnl.setLoading(false);

    },

    /**
     * Removes any previous result panels.
     */
    removePreviousResults: function() {
        var parentCt,
            prevResults = Ext.ComponentQuery.query('#results'),
            i = 0,
            len = prevResults.length;

        // remove any previous results
        for (; i<len; i++) {
            parentCt = prevResults[i].ownerCt;
            parentCt.remove(prevResults[i]);
            parentCt.up('panel').doLayout();
        }
    },


    /**
     * Shows a panel with an embedded google map using Google's Map JavaScript API V3.
     * More information on the Google Map JavaScript API can be found here [http://code.google.com/apis/maps/documentation/javascript/](http://code.google.com/apis/maps/documentation/javascript/)
     */
    showResultsMap: function(options) {
        var me = this,
            frm = Ext.ComponentQuery.query('#' + options.resultsFormId)[0],
            pnl = frm.up('panel'),
            gm = (window.google || {}).maps,
            coordinates = options.results,
            latlng = new gm.LatLng(coordinates.latitude, coordinates.longitude),
            mapPanel, mapCmp, marker,
            results = options.results,
            resultType = options.resultType || 'success';


        // remove any previous results (just in case)
        me.removePreviousResults();

        // add a container to the form to show the results
        mapPanel = frm.add({
            itemId      : 'results',
            xtype       : 'panel',
            cls         : resultType + '-panel',
            padding     : 4,
            margin      : '4 0 0 0',
            height: Ext.is.Phone ? 320 : 500,
            dockedItems : new Ext.Toolbar({ dock : 'top', title: resultType.toUpperCase()}),
            items       : [{ xtype: 'map' }]
        });
        mapCmp = mapPanel.down('map'),

        mapCmp.mapOptions = {
            center    : latlng,
            zoom      : 12
        }

        // update the google map with the coords of device
        mapCmp.update(coordinates);

        // now add a marker to where the device is
        mapCmp.on('afterrender', function(mapCmp) {
            new gm.Marker({
                position: latlng,
                map: mapCmp.map,
                title: options.address
            });
        });


        // force a doLayout so we can see the changes
        pnl.doLayout();

        // hide any masks
        pnl.setLoading(false);

    }


});