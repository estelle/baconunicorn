(function(){
    try {
        /**
         * @class SampleApps.MMS.App3.stores.Preferences
         *
         * Store used to hold Preference model instances.
         *
         * @extends Ext.data.Store
         */
        Ext.regStore('Preferences', {

            /**
             * Uses the SampleApps.MMS.App3.models.Preference model
             */
            model       : 'Preference',
            autoLoad    : true

        });
    } catch(e){
        // if we get here it is usually due to private browsing
        // turned on in iOS and local/session storage doesn't like that
    }
})();