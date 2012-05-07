/**
 * @class SampleApps.MMS.App3.stores.Images
 *
 * Store used to hold Image model instances.
 *
 * @extends Ext.data.Store
 */
Ext.regStore('Images', {

    /**
     * Uses the SampleApps.MMS.App3.models.Image model
     */
    model       : 'Image',
    autoLoad    : true

});