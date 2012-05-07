(function(){
    try {
        /**
         * @class SampleApps.MMS.App3.models.Preference
         *
         * Model used in the Preferences panel.
         *
         * @extends Ext.data.Model
         */
        Ext.regModel('Preference', {

            idProperty  : 'id',

            /**
             * The fields that make up this Model
             */
            fields : [
                { name  : 'id', type : 'number' },
                { name  : 'address' },
                { name  : 'theme' }
            ],

            /**
             * The proxy use by this Model
             */
            proxy   : {
                type    : 'localstorage',
                id      : 'att-sample-preferences'
            }

        });
    } catch(e){
        // if we get here it is usually due to private browsing
        // turned on in iOS and local/session storage doesn't like that
    }
})();