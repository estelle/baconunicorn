/**
 * @class SampleApps.MMS.App3.models.Image
 *
 * Model used in the MMS Gallery app.
 *
 * @extends Ext.data.Model
 */
Ext.regModel('Image', {
    /**
     * The fields that make up this Model
     */
    fields : [
        { name : 'path' },
        { name : 'senderAddress' },
        { name : 'date' },
        { name : 'text' }
    ],

    /**
     * The proxy use by this Model
     */
    proxy   : {
        type    : 'ajax',
        url     : SampleApps.MMS.App3.config.getImageDataUri,
        reader  : {
            type : 'json',
            root : 'imageList',
            totalProperty : 'totalNumberOfImagesSent'
        }
    }

});