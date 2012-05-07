/**
 * @class SampleApps.MMS.App3
 *
 * # App3 - MMS Gallery app
 *
 * This application allows a user to send an MMS message from his/her mobile device to a short code displayed in the application.
 * This delivers the message to the application instantly, which creates a photo gallery.
 * The interface allows the user to view the photo gallery of all MMS images and associated text sent to the application.
 *
 */
Ext.regApplication({

    name                : 'SampleApps.MMS.App3',
    defaultRenderTarget : 'viewport',

    // Do not set the defaultUrl since ST will add this
    // as a hash when you navigate to the 'home' page.
    // and this will then break the browser's 'back button
    //defaultUrl          : 'index',

    glossOnIcon         : true,
    icon                : 'assets/images/icon_develop.png',
    tabletStartupScreen : 'assets/images/tabletStartupScreen.png',
    phoneStartupScreen  : 'assets/images/phoneStartupScreen.png',

    launch: function() {

        Ext.dispatch({
            controller  : 'Main',
            action      : 'launchApp'
        });

        Ext.dispatch({
            controller : 'App',
            action     : 'openAppStandAlone',
            xtype      : 'att-mms-gallery'
        });

    }

});
