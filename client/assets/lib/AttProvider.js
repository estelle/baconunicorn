/**
 * The Att namespace (global object) for the AT&T Provider class.
 * @singleton
 */
Att = {};

/**
 * @class Att.Provider
 * Att.Provider exposes methods to access the AT&T APIs.
 * When a method is called on the client side, a request is made to the server side.
 * The server will validate the request and then make the appropriate call
 * to the AT&T API.
 *


Init
----

From client/app/app.js

    this.provider = new Att.Provider();

Or, if you need to change what the apiBasePath is:

    this.provider = new Att.Provider({
        apiBasePath : '/your/base/path'
    });

Authentication
---
The SDK Authorization Method supports three approaches: On-Network Authentication, MSISDN/PIN Authentication, and Username/Password Consent.
This method is required and supported only for applications attempting to consume and access the Terminal Location API (DL) or Device Capabilities API (DC).
To use SMS, MMS, and WAP PUSH APIs, user can authorize and send messages through the Client Credential method, which is the automatic OAuth model.


Automatic (OAuth Model - Client Credential)
----

When calling SMS, MMS or WAP Push, the SDK server will request an authorization token from AT&T using your application credentials, and will make the API call automatically.  The user of the application will not need to explicitly authorize the action and you can send messages to any valid
AT&T MSISDN.


Login  (OAuth Model - Authorization Code)
----
For the Device Location and Device Info API calls you will need explicit permission from the user to access information about their device.
The SDK provides api calls to check if the user is currently authorized. If they are not, the API will create an iframe and redirect the user to the OAUTH login sequence.
After that, the user will have a valid access token associated with their session.


    // isAuthorize checks to see if the user has a valid auth token stored on the SDK server
    // If the user has a valid token we don't need to ask the user to re-authorize.

    this.provider.isAuthorized("TL,DC", {

      success: function() {
           // On successful authorization, proceed to the next step in the application.
       },

       failure: function() {
           // We don't have a valid token on the SDK server.
           // Ask the user to login and authorize this application to process payments.
           // This will pop up an AT&T login followed by an authorization screen.
           KitchenSink.provider.authorizeApp(self.authScope, {

               success: function() {
                   //On successful authorization, proceed to the next step in the application.
               },

               failure: function() {
                   console.log("failure arguments", arguments);
               }

           });

       }

     });

Payment
---

The Payment API uses a combination of Client Credential authorization and a user prompt similar to oauth.
When initiating a payment request, the SDK server uses the Client Credential authorization token to retrieve a one-time-use url, which is automatically presented to the user in an iframe in the same way the authorizeApp provider call does for oauth logins.
After the user has completed the payment process, and the application has the transaction id of the purchase, it can then use the Client Credential authorization token to get the status of a payment.

Making API Calls
---

Call the provider API method with the required parameters.
On success you will receive a JSON encoded response from the server.
This data is identical to the data returned by the APIs from AT&T.

    this.provider.sendSms({
       address : 'SOMEPHONENUMBER',
       message : 'your sms message', {
       success : function(response) {
           self.setLoading(false);
           KitchenSink.showResults(response, "SMS Sent");
           self.smsId = response.Id;
           Ext.getCmp('sms-status-button').enable();
       },
       failure : function(error) {
           console.log("failure", error);
           self.setLoading(false);
           Ext.Msg.alert('Error', error);
       }
   });


Error Handling
----

In case an exception or an error happens, detailed information on the exception/error is available in the error property of the response

 *
 */
Att.Provider = function(cfg){

    /**
     *
     * @cfg {String} apiBasePath
     * The base uri path to be prepended to all API calls (defaults to blank).

     This helps solve the issue of the "same origin policy". If you have
     the server component configured to run on a port different than the web application
     since your web application will typically run on port 80, you may have the
     server component configured to run on port 4567 or 8080.

     For example, if you have decided to implement the Ruby server component and are
     letting the ruby app listen on port 4567, you could define your reverse proxy
     like so:

           ProxyPass /att/rubyapi/ http://localhost:4567/
           ProxyPassReverse /att/rubyapi/ http://localhost:4567/

     Then, when you initialize an instance of the Att.Provider you would do the following:

           this.provider = new Att.Provider({
              apiBasePath : '/att/rubyapi'
          });

     Thus, all calls from the web client that calls Att.Provider apis will originate from
     the same hostname:port and will be sent to /att/rubyapi/the/api/uri, but the
     reverse proxy will reroute these calls on the server to http://localhost:4567/the/api/uri.

     For example:

     Browser sends:

        http://yourserver.com/att/callback

     Server reroutes to:

        http://localhost:4567/att/callback
     *
     */
    this.apiBasePath = '';
    this.serviceProvider = {};
    Ext.apply(this,cfg);

    this.apiBasePath += '/att';
    var me = this;
    window.addEventListener('message', function(event) { me.handleMessage(event.data)}, false);

    Ext.Direct.addProvider({
        "type":"remoting",                          // create a Ext.direct.RemotingProvider
        "url": this.apiBasePath + "/direct_router", // url to connect to the Ext.Direct server-side router.
        "actions":{                                 // each property within the actions object represents a Class
            "ServiceProvider": [                    // array of methods within each server side Class
                {
                    "name": "oauthUrl",
                    "len": 1
                },
                {
                    "name": "deviceInfo",
                    "len": 1
                },
                {
                    "name": "deviceLocation",
                    "len": 4
                },
                {
                    "name": "sendSms",
                    "len": 2
                },
                {
                    "name": "smsStatus",
                    "len": 1
                },
                {
                    "name": "receiveSms",
                    "len": 0
                },
                {
                    "name": "sendMms",
                    "len": 4
                },
                {
                    "name": "mmsStatus",
                    "len": 1
                },
                {
                    "name": 'wapPush',
                    "len": 4
                },
                {
                    "name": 'requestChargeAuth',
                    "len": 2
                },
                {
                    "name": "transactionStatus",
                    "len": 2
                },
                {
                    "name": "subscriptionStatus",
                    "len": 2
                },
                {
                    "name": "refundTransaction",
                    "len": 2
                },
                {
                    "name": "subscriptionDetails",
                    "len": 2
                },
                {
                    "name": "signPayload",
                    "len": 1
                }
            ]
        },
        "namespace":"Att"
    });

    var self = this;
    for(var method in Att.ServiceProvider) {
        if (Att.ServiceProvider.hasOwnProperty(method)) {
            (function() {
                var scopedMethod = method;
                self.serviceProvider[scopedMethod] = function() {
                    var configObj = arguments[arguments.length - 1];
                    var selfArgs = Array.prototype.slice.call(arguments).slice(0, arguments.length - 1);

                    self.successCallback = configObj.success;
                    self.errorCallback = configObj.failure;

                    selfArgs.push(function(result, evt) {

                        if(evt.status) {
                            configObj.success(result);
                        } else {
                            if (evt.error) {
                                configObj.failure(evt.error);
                            } else {
                                var xerr = { xhrError : { status : '500', statusText : 'Internal Server Error'} };
                                if (evt.xhr) {
                                    xerr = {
                                        xhrError : {
                                            status : evt.xhr.status,
                                            statusText : evt.xhr.statusText
                                        }
                                    };
                                }
                                configObj.failure(xerr);
                            }
                        }
                    });

                    Att.ServiceProvider[scopedMethod].apply(Att.ServiceProvider[scopedMethod], selfArgs);
                }
            })();
        }
    }
};

Att.Provider.prototype = {

    /**
     * Checks to see if the app is authorized against the given authScopes.
     *
     * @param {Object} options An object which may contain the following properties.
     *   @param {String} options.authScope Comma separated list of authScopes the app requires access to.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    isAuthorized: function(options) {

        this.successCallback = options.success;
        this.failureCallback = options.failure;

        var self = this;

        Ext.Ajax.request({
            url: this.apiBasePath + '/check?scope=' + options.authScope,
            method: 'GET',
            success: function(response){
                var jsonResponse = JSON.parse(response.responseText)
                if (jsonResponse.authorized) {
                    self.successCallback();
                } else {
                    self.failureCallback();
                }
            },
            failure: function(response){
                self.failureCallback(response);
            }
        });
    },

    /**
     * Initiate client authorization window for the user to authorize the application
     * against the given authScopes.
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.authScope Comma separated list of authScopes the app requires access to.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    authorizeApp: function(options) {

        this.successCallback = options.success;
        this.failureCallback = options.failure;

        var self = this;

        // this is different than the other api calls
        // where we call the method from the provider.serviceProvider 'instance'
        // whereas here, we just call the method directly on the Att.ServiceProvider class
        Att.ServiceProvider.oauthUrl(
            options.authScope,
            function(result, e) {
                self.createIframe(result);
            }
        );
    },

    /**
     * Requests a one-time payment based on the options passed.
     * This method call will present a pop-up to the user where they
     * will authorize the transaction with AT&T.
     * When the user authorizes the request or cancels it, the success callback appears
     * with the payment details.
     *

        var charge = { "Amount":0.99,
              "Category":1,
              "Channel":"MOBILE_WEB",
              "Description":"better than level 1",
              "MerchantTransactionId":"skuser2985trx20111029175423",
              "MerchantProductId":"level2"}


        provider.requestPayment({
            paymentOptions : charge,
            success : successCallback,
            failure : failureCallback
        });
    *
    *  See AT&T payment documentation for a complete set of payment options and restrictions
    *
    * @param {Object} options An object which may contain the following properties:
    *   @param {Object} options.paymentOptions payment options
    *   @param {Function} options.success success callback function
    *   @param {Function} options.failure failure callback function
    */
    requestPayment: function(options) {

        // requestPayment calls the private method _chargeIt and
        // passes type as 'SINGLEPAY'
        Ext.applyIf(options, {
            type : 'SINGLEPAY'
        });

        this._chargeIt(options);
    },


    /**
     * Requests a subscription based on the payment options passed.
     * this method call will present a popup to the user where they
     * will authorize the transaction with AT&T.
     * When the user authorizes the request or cancels it, the success callback appears
     * with the payment details.
     *
     *
     *  Success callback example (when payments work)

        function(results) { console.log("payment worked!", results);}

     *  Failure callback examples (when the user cancels or the payment doesn't complete)

        function(results) { console.log("payment worked!", results.error, results.error_reason, results.error_description);}

     *  in the case of user cancel you will get something like this:

        error: "access_denied"
        error_description: "The user denied your request"
        error_reason: "user_denied"

     * @param {Object} options An object which may contain the following properties:
     *   @param {Object}  options.paymentOptions payment options

         var charge = {
            "Amount":0.99,
            "Category":1,
            "Channel":"MOBILE_WEB",
            "Description":"better than level 1",
            "MerchantTransactionId":"skuser2985trx20111029175423",
            "MerchantProductId":"level2"
        }

        provider.requestPaidSubscription({
            paymentOptions : charge,
            success : successCallback,
            failure : failureCallback
        });
     *
     *  See AT&T payment documentation for a complete set of payment options and restrictions
     *
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    requestPaidSubscription: function(options) {

        // requestPaidSubscription calls the private method _chargeIt and
        // passes type as 'SUBSCRIPTION'
        Ext.applyIf(options, {
            type : 'SUBSCRIPTION'
        });

        this._chargeIt(options);
    },

    /**
     * Encrypts the payload param so that it can be used in other Payment API calls
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {Object} options.payload The JSON payload that you want to sign.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    signPayload: function(options) {
        var me = this;

        me.serviceProvider.signPayload(
            options.payload,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Given an authScope, returns the corresponding AT&T oAuth URL
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.authScope a comma separated list of services that the app requires access to
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    oauthUrl: function(options){
        var me = this;

        me.serviceProvider.oauthUrl(
            options.authScope,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Returns information on a device
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.address MSISDN of the device to query
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    deviceInfo: function(options){
        var me = this;

        me.serviceProvider.deviceInfo(
            options.address,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Returns location info for a device
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.address MSISDN of the device to query
     *   @param {Number} options.requestedAccuracy The requested accuracy is given in meters. This parameter shall be present in the resource URI as query parameter. If the requested accuracy cannot be supported, a service exception (SVC0001) with additional information describing the error is returned.  Default is 100 meters.
     *   @param {Number} options.acceptableAccuracy The acceptable accuracy is given in meters and influences the type of location service that is used. This parameter shall be present in the resource URI as query parameter.
     *   @param {Number} options.tolerance This parameter defines the application's priority of response time versus accuracy.
     *
     * Valid values are:
     *
     * - **NoDelay** No compromise on the priority of the response time over accuracy
     * - **LowDelay** The response time could have a minimum delay for a better accuracy
     * - **DelayTolerant** Response time could be compromised to have high delay for better accuracy
     *
     * Note :If this parameter is not passed in the request, the default value is LowDelay.
     *
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     *
     * Usage:
     *
        KitchenSink.provider.deviceLocation({
            address: deviceLocationInput.getValue(),
            requestedAccuracy: 1000
        });
     *
     */
    deviceLocation: function(options){
        var me = this;

        // apply defaults
        Ext.applyIf(options, {
            requestedAccuracy : -1,
            acceptableAccuracy : -1,
            tolerance : 'LowDelay'
        });

        me.serviceProvider.deviceLocation(
            options.address,
            options.requestedAccuracy,
            options.acceptableAccuracy,
            options.tolerance,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Sends an SMS to a recipient
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.address The MSISDN of the recipient(s). Can contain comma separated list for multiple recipients.
     *   @param {String} options.message The text of the message to send
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    sendSms: function(options){
        var me = this;

        me.serviceProvider.sendSms(
            options.address,
            options.message,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Checks the status of a sent SMS
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.smsId The unique SMS ID as retrieved from the response of the sendSms method
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    smsStatus: function(options){
        var me = this;

        me.serviceProvider.smsStatus(
            options.smsId,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Retrieves a list of SMSs sent to the application's short code
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    receiveSms: function(options){
        var me = this;

        me.serviceProvider.receiveSms(
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Sends an MMS to a recipient
     *
     *  MMS allows for the delivery of different file types please see the [developer documentation](https://developer.att.com/developer/tierNpage.jsp?passedItemId=2400428) for an updated list.
     *
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.address The MSISDN of the recipient(s). Can contain comma separated list for multiple recipients.
     *   @param {String} options.fileId The reference to a file to be sent in the MMS.  The server will map the fileId to a real file location.
     *   @param {String} options.message The text of the message to send.
     *   @param {String} options.priority The priority of the message.
     *
     * Valid values are:
     *
     * - **Low**
     * - **Normal**
     * - **High**
     *
     * Note :If this parameter is not passed in the request, the default value is Normal.
     *
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    sendMms: function(options){
        var me = this;

        // apply defaults
        Ext.applyIf(options, {
            priority : "Normal"
        });

        me.serviceProvider.sendMms(
            options.address,
            options.fileId,
            options.message,
            options.priority,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Checks the status of a sent MMS
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.mmsId The unique MMS ID as retrieved from the response of the sendMms method
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    mmsStatus: function(options){
        var me = this;

        me.serviceProvider.mmsStatus(
            options.mmsId,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     * Sends a WAP Push message
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.address The MSISDN of the recipient(s). Can contain comma separated list for multiple recipients.
     *   @param {String} options.message The XML document containing the message to be sent.
     *   @param {String} options.subject The subject of the message
     *   @param {String} options.priority the priority of the message.
     *
     * Valid values are:
     *
     * - **Low**
     * - **Normal**
     * - **High**
     *
     * Note :If this parameter is not passed in the request, the default value is Normal.
     *
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     * @method wapPush
     */
    wapPush: function(options){
        var me = this;

        // apply defaults
        Ext.applyIf(options, {
            priority : "Normal"
        });

        me.serviceProvider.wapPush(
            options.address,
            options.message,
            options.subject,
            options.priority,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },

    /**
     *  requestChargeAuth sends a charge request to the server.
     *  The server returns a redirect url where the user can verify
     *  the charge and authorize the transaction.
     *
     *  Normally you should use Att.Provider.requestPayment or requestSubscription
     *  as it will handle the user redirect and fire your callback
     *  once the payment has been authorized.
     *  Usage:

        var charge = {
            "Amount":0.99,
            "Category":1,
            "Channel":"MOBILE_WEB",
            "Description":"better than level 1",
            "MerchantTransactionId":"skuser2985trx20111029175423",
            "MerchantProductId":"level2"
        }

        requestChargeAuth({
            type: "payment",
            paymentOptions: charge,
            success: successCallback,
            failure: failureCallback
        });

     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.type the type of payment being requested (single or subscription)
     *   @param {Object} options.paymentOptions payment options. See AT&T payment documentation for a complete set of payment options and restrictions.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    requestChargeAuth: function(options){
        var me = this;

        me.serviceProvider.requestChargeAuth(
            options.type,
            options.paymentOptions,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },


    /**
     * Checks the status of a transaction.
     *
     * @param {Object} options An object which may contain the following properties:
     * @param {String} options.codeType String for the type of transaction id being passed  can be "TransactionAuthCode" or "MerchantTransactionId" or "TransactionId"
     *   @param {String} options.transactionId transaction authorization code to check can be the transaction auth code, merchant transasction id or transaction id.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    transactionStatus: function(options){
        var me = this;

        me.serviceProvider.transactionStatus(
            options.codeType,
            options.transactionId,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },


    /**
     * Checks the status of a subscription.
     *
     * @param {Object} options An object which may contain the following properties:
     * @param {String} options.codeType String for the type of transaction id being passed  can be "SubscriptionAuthCode" or "MerchantTransactionId" or "SubscriptionId"
     *   @param {String} options.transactionId Subscription authorization code to check can be the Subscription auth code, merchant transaction id or Subscription id.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    subscriptionStatus: function(options){
        var me = this;
        
        
        /*
        * Workaround for AT&T API returing invalid JSON (QC#2814) that contains duplicate  
        * MerchantSubscriptionId.  The server returns an error but we can parse the
        * error data using JSON.parse which is much more forgiving. 
        * This code needs to be removed when 2814 is fixed. 
        */
        var success = function(results) {
           if(results.error == "Invalid JSON in response") {
              results = JSON.parse(results.error_details);
            }
            if(options.success) {
              options.success(results);
            }
        }

        me.serviceProvider.subscriptionStatus(
            options.codeType,
            options.transactionId,
            {
                success : success,
                failure : options.failure
            }
        );
    },


    /**
     * Issues a request to refund a transaction
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.transactionId transaction id to revoke.
     *   @param {Object} options.refundOptions refund options. See AT&T payment documentation for a complete set of refund options and restrictions.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    refundTransaction: function(options){
        var me = this;

        me.serviceProvider.refundTransaction(
            options.transactionId,
            options.refundOptions,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },


    /**
     * Checks the details of subscription
     *
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.merchantSubscriptionId authorization code of the subscription.
     *   @param {String} options.consumerId id of the user who created the subscription
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     */
    subscriptionDetails: function(options){
        var me = this;

        me.serviceProvider.subscriptionDetails(
            options.merchantSubscriptionId,
            options.consumerId,
            {
                success : options.success,
                failure : options.failure
            }
        );
    },



    /**
     * @param {Object} options An object which may contain the following properties:
     *   @param {String} options.type The type of charge.  Valid values are 'SINGLEPAYMENT' and 'SUBSCRIPTION'
     *   @param {Object} options.paymentOptions payment options. See AT&T payment documentation for a complete set of payment options and restrictions.
     *   @param {Function} options.success success callback function
     *   @param {Function} options.failure failure callback function
     * @private
     */
    _chargeIt: function(options) {
        var self = this;

        this.successCallback = options.success;
        this.failureCallback = options.failure;

        this.serviceProvider.requestChargeAuth(
            options.type,
            options.paymentOptions,
            {
                success : function(results, e) {
                    if(results.adviceOfChargeUrl){
                        // an adviceOfChargeUrl is returned when a consent flow needs to be followed
                        // so instead of calling the defined success handler, we create an iframe
                        // and load the adviceOfChargeUrl
                        self.createIframe(results.adviceOfChargeUrl, {});
                    } else {
                        options.success(results);
                    }
                },
                failure : function(results)   {
                    options.failure(results);
                }
            }
        );
    },

    /**
     * @private
     */
    createIframe: function(src, options) {

        var me = this;
        me.redirect = src;

        // create the iframeOverlay (if it isn't already been created)
        me.iframeOverlay = new Ext.Panel({
            floating: true,
            modal: true,
            centered: true,
            //fullscreen: true,
            width: Ext.is.Phone ? 320 : 500,
            height: Ext.is.Phone ? 320 : 500
        });
        me.iframeOverlay.on('hide', function(){
            me.iframeOverlay.removeAll(true);
            Ext.removeNode(me.iframeEl);
            delete me.iframeEl
            me.iframeOverlay.destroy()
            delete me.iframeOverlay;

            if (me.failureCallback) {
                me.failureCallback({
                    success           : false,
                    error             : 'consent_page_closed',
                    error_reason      : 'user_closed_consent_page',
                    error_description : 'The user closed the consent page'
                });
            }

            // make sure to delete old callback references
            delete me.successCallback;
            delete me.failureCallback;

        }, me);


        me.iframeEl = document.createElement('iframe');
        Ext.fly(me.iframeEl).set({
            id      : 'providerIframe',
            name    : 'providerIframe',
            align   : 'middle',
            style   : 'display: block; overflow: hidden; width: 100%; height: 100%'

        });
        document.body.appendChild(me.iframeEl);

        me.iframeOverlay.add({ xtype: 'container', contentEl: me.iframeEl});
        me.iframeOverlay.show();
        me.iframeEl.src =  me.redirect;

    },

    /**
     * @private
     */
    handleMessage: function(msg) {

        if(msg.indexOf("{") == 0) {
            try {
                var message = JSON.parse(msg),
                    callback =  message.success === true ? this.successCallback : this.failureCallback;

                delete this.successCallback;
                delete this.failureCallback;

                this.iframeOverlay.hide();

                if(callback) {
                    callback(message);
                }
            } catch(e) {
                console.log("messsage was not JSON ", msg, e);
            }
        }

    },

    /**
     * @class Att.Provider.util
     */
    util: {

        /**
         * Given a phone number, returns true or false if the phone number is in a valid format.
         * @param {String} phone the phone number to normalize
         * @return {Boolean}
         */
        isValidPhoneNumber: function(phone) {
            return /^(1?(-?\d{3})-?)?(\d{3})(-?\d{4})$/.test(phone);
        },

        /**
         * Given a phone number, returns the phone number with all characters, other than numbers, stripped
         * @param {String} phone the phone number to normalize
         * @return {String} the normalized phone number
         */
        normalizePhoneNumber: function(phone) {
            phone = phone.toString(); // make sure we have a string
            return phone.replace(/[^\d]/g, "");
        }

    }


}