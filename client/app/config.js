/**
 * @class SampleApps.MMS.App3.config
 *
 * Configuration for AT&T Sample Applications
 *
 */
Ext.ns('SampleApps.MMS.App3.config');

Ext.apply(SampleApps.MMS.App3.config, {

    /**
     * apiBasePath is used as the root path to make the SenchProvider api calls
     * so this path can be blank, relative, or absolute.
     */
    apiBasePath   : '',
    //apiBasePath : '/att/javaapi',
    //apiBasePath : '/att/rubyapi',
    //apiBasePath : '/att/phpapi',



    /**
     * url of where to get the json data relative to where the app is installed
     */
    getImageDataUri      : 'assets/data/gallery.json',
    //'getImageDataUri'  : 'getImageData.jsp',



    /**
     * gallery images folder, relative to where the app is installed
     */
    galleryImagesFolder    : 'assets/data/gallery/',
    //galleryImagesFolder  : '',

    /**
     * coupon folder, relative to where the app is installed
     */
    couponImagesBaseUri   : 'assets/data/coupons/',



    shortCode         : 80712765,
    defaultPhoneNbr   : 4157108526,
    errorTitle        : 'ERROR',
    successTitle      : 'SUCCESS',
    invalidPhoneMsg   : 'Phone number is not valid.  Please re-enter. <br/>Example: 14258028620, 425-802-8620, 4258028620',
    defaultMessage    : 'Simple message to myself.',
    defaultWapMessage : 'This is a sample WAP Push message.',
    defaultWapUrl     : 'http://developer.att.com',
    maxTotalFileSize  : 600 * 1024 // 600K

});
