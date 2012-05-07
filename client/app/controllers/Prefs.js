/**
 * Controller for SampleApps.MMS.App3.views.PrefsPanel
 *
 * @class SampleApps.MMS.App3.controllers.Prefs
 *
 * @extends Ext.Controller
 */
Ext.regController('Prefs', {

    /**
     * Returns to the Prefs Panel home page (#index)
     */
    openPrefs: function() {
        Ext.dispatch({
            controller : 'Prefs',
            action     : 'index',
            historyUrl : 'Prefs/index'
        });
    },

    index: function() {
        var me = this,
            app = me.application,
            viewport = app.viewport,
            newCard;

        newCard = viewport.add({
            xtype : 'att-prefspanel',
            listeners   : {
                scope       : me,
                afterrender : me.onAfterRender
            }
        });
        viewport.setActiveItem(newCard, { type : 'slide', direction : 'up' });
    },

    /**
     * Called after the Prefs Panel has rendered and sets up the tap listeners on the 'Done' and 'Save' buttons.
     */
    onAfterRender: function(pnl) {
        var me = this,
            app = me.application,
            frm = pnl.down('form'),
            btnDone = pnl.down('toolbar button[itemId=btnDone]'),
            btnSave = pnl.down('button[itemId=btnSave]');

        // load the prefs data into the form
        frm.setValues(app.prefs);

        // setup our btn tap listeners
        btnDone.on('tap', me.onDoneBtnTap, me);
        btnSave.on('tap', me.onSaveBtnTap, me);
    },

    /**
     * Method called when the user taps on the 'Done' button.
     * It first checks to see if a previous item exists and if so, makes this item the active item.
     * If it cannot find a previous item, this method dispatches to the App controllers 'openHome' method
     */
    onDoneBtnTap: function(btn) {
        var me = this,
            viewport = btn.up('#viewport'),
            layout = viewport.getLayout(),
            prev;

        prev = layout.getPrev();
        if (prev) {
            viewport.setActiveItem(prev);
        } else {
            Ext.dispatch({
                controller  : 'App',
                action      : 'openHome'
            });
        }

    },

    /**
     * Method called when the user taps on the 'Save' button.
     * It first saves the preferences to local storage.
     * If the theme has changed, the #switchTheme method is called
     */
    onSaveBtnTap: function(btn) {
        var me = this,
            app = me.application,
            frm = btn.up('form'),
            localStore = Ext.StoreMgr.get('Preferences'),
            prefsRec, theme;

        // save the prefs to localstorage (if local storage is available)
        if (localStore) {
            prefsRec = Ext.ModelMgr.create(frm.getValues(), 'Preference', 1);
            prefsRec.save();


            // update the theme now if it has changed
            theme = prefsRec.get('theme');
            if (theme !== '' && theme !== app.prefs.theme) {
                me.switchTheme(theme);
            }

            // save the data in prefsRec to the application.prefs property
            app.prefs = prefsRec.data;
        } else {
            Ext.Msg.alert('Error',"Unable to save preferences.  This can be caused by using Safari's 'Private Browsing' mode.")
        }

    },


    /**
     * Sitches the them by calling the #swapStyleSheet method
     */
    switchTheme: function(args){
        var me = this,
            cssUrl;

        if (Ext.isObject(args)) {
            theme = args.theme;
        } else {
            theme = args;
        }

        cssUrl = Ext.util.Format.format('assets/css/{0}.css', theme);

        me.swapStyleSheet('app-css-theme', cssUrl);

    },


    /**
     * Removes a style or link tag by id
     * @param {String} id The id of the tag
     */
    removeStyleSheet : function(id) {
        var existing = document.getElementById(id);
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
    },

    /**
     * Dynamically swaps an existing stylesheet reference for a new one
     * @param {String} id The id of an existing link tag to remove
     * @param {String} url The href of the new stylesheet to include
     */
    swapStyleSheet : function(id, url) {
        var doc = document;
        this.removeStyleSheet(id);
        var ss = doc.createElement("link");
        ss.setAttribute("rel", "stylesheet");
        ss.setAttribute("type", "text/css");
        ss.setAttribute("id", id);
        ss.setAttribute("href", url);
        doc.getElementsByTagName("head")[0].appendChild(ss);
    }


});