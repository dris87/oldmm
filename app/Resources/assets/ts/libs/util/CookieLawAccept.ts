import * as $ from 'jquery';
import { SystemConfig } from '../SystemConfig';
import { setTimeout } from 'timers';
import {Framework} from "../Framework";

/**
 * This library will enable the cookie law accept bar
 */
export class CookieLawAccept{

    /**
     *
     * @type {boolean}
     */
    private debug = false;

    /**
     * 
     * @type CookieLawAcceptOptions
     */
    private options:CookieLawAcceptOptions = {
        cookiePolicyUrl : 'http://www.wimagguc.com/?cookie-policy',
        popupPosition : 'bottom',
        colorStyle : 'default',
        compactStyle : false,
        popupTitle : 'This website is using cookies',
        popupText : 'We use cookies to ensure that we give you the best experience on our website. If you continue without changing your settings, we\'ll assume that you are happy to receive all cookies on this website.',
        buttonContinueTitle : 'Continue',
        buttonLearnmoreTitle : 'Learn&nbsp;more',
        buttonLearnmoreOpenInNewWindow : true,
        agreementExpiresInDays : 30,
        autoAcceptCookiePolicy : false,
        htmlMarkup : null
    };

    /**
     * 
     * @type {boolean}
     */
    private initialised:Boolean = false;

    /**
     * 
     * @type {null}
     */
    private html_markup:any = null;

    /**
     * 
     * @type {string}
     */
    private cookie_name:string = 'EU_COOKIE_LAW_CONSENT';

    /**
     * 
     * @param {CookieLawAcceptOptions} options
     */
    public constructor( options:CookieLawAcceptOptions ){

        this.debug = SystemConfig.debug;

        this.parseParameters(
            $(".eupopup").first(),
            $(".eupopup-markup").html(),
            options);

        this.publicfunc();

        $(document).bind("user_cookie_consent_changed", function(event, object) {
            console.log("User cookie consent changed: " + $(object).attr('consent') );
        });

    }
    
    // Overwrite default parameters if any of those is present
    public  parseParameters(object:object, markup:string, settings:CookieLawAcceptOptions) {

        if (object) {
            let className = $(object).attr('class') ? $(object).attr('class') : '';
            if (className.indexOf('eupopup-top') > -1) {
                this.options.popupPosition = 'top';
            }
            else if (className.indexOf('eupopup-fixedtop') > -1) {
                this.options.popupPosition = 'fixedtop';
            }
            else if (className.indexOf('eupopup-bottomright') > -1) {
                this.options.popupPosition = 'bottomright';
            }
            else if (className.indexOf('eupopup-bottomleft') > -1) {
                this.options.popupPosition = 'bottomleft';
            }
            else if (className.indexOf('eupopup-bottom') > -1) {
                this.options.popupPosition = 'bottom';
            }
            else if (className.indexOf('eupopup-block') > -1) {
                this.options.popupPosition = 'block';
            }
            if (className.indexOf('eupopup-color-default') > -1) {
                this.options.colorStyle = 'default';
            }
            else if (className.indexOf('eupopup-color-inverse') > -1) {
                this.options.colorStyle = 'inverse';
            }
            if (className.indexOf('eupopup-style-compact') > -1) {
                this.options.compactStyle = true;
            }
        }

        if (markup) {
            this.options.htmlMarkup = markup;
        }

        if (settings) {
            if (typeof settings.cookiePolicyUrl !== 'undefined') {
                this.options.cookiePolicyUrl = settings.cookiePolicyUrl;
            }
            if (typeof settings.popupPosition !== 'undefined') {
                this.options.popupPosition = settings.popupPosition;
            }
            if (typeof settings.colorStyle !== 'undefined') {
                this.options.colorStyle = settings.colorStyle;
            }
            if (typeof settings.popupTitle !== 'undefined') {
                this.options.popupTitle = settings.popupTitle;
            }
            if (typeof settings.popupText !== 'undefined') {
                this.options.popupText = settings.popupText;
            }
            if (typeof settings.buttonContinueTitle !== 'undefined') {
                this.options.buttonContinueTitle = settings.buttonContinueTitle;
            }
            if (typeof settings.buttonLearnmoreTitle !== 'undefined') {
                this.options.buttonLearnmoreTitle = settings.buttonLearnmoreTitle;
            }
            if (typeof settings.buttonLearnmoreOpenInNewWindow !== 'undefined') {
                this.options.buttonLearnmoreOpenInNewWindow = settings.buttonLearnmoreOpenInNewWindow;
            }
            if (typeof settings.agreementExpiresInDays !== 'undefined') {
                this.options.agreementExpiresInDays = settings.agreementExpiresInDays;
            }
            if (typeof settings.autoAcceptCookiePolicy !== 'undefined') {
                this.options.autoAcceptCookiePolicy = settings.autoAcceptCookiePolicy;
            }
            if (typeof settings.htmlMarkup !== 'undefined') {
                this.options.htmlMarkup = settings.htmlMarkup;
            }
        }

    };

    private createHtmlMarkup() {

        if (this.options.htmlMarkup) {
            return this.options.htmlMarkup;
        }

        let html:string =
            '<div class="eupopup-container' +
            ' eupopup-container-' + this.options.popupPosition +
            (this.options.compactStyle ? ' eupopup-style-compact' : '') +
            ' eupopup-color-' + this.options.colorStyle + '">' +
            '<div class="eupopup-head">' + this.options.popupTitle + '</div>' +
            '<div class="eupopup-body">' + this.options.popupText + '</div>' +
            '<div class="eupopup-buttons">' +
            '<a href="#" class="eupopup-button eupopup-button_1">' + this.options.buttonContinueTitle + '</a>' +
            '<a href="' + this.options.cookiePolicyUrl + '"' +
            (this.options.buttonLearnmoreOpenInNewWindow ? ' target=_blank ' : '') +
            ' class="eupopup-button eupopup-button_2">' + this.options.buttonLearnmoreTitle + '</a>' +
            '<div class="clearfix"></div>' +
            '</div>' +
            '<a href="#" class="eupopup-closebutton">x</a>' +
            '</div>';

        return html;
    };

    // Storing the consent in a cookie
    private setUserAcceptsCookies(consent:any) {
        let d = new Date();
        let expiresInDays = this.options.agreementExpiresInDays * 24 * 60 * 60 * 1000;
        d.setTime( d.getTime() + expiresInDays );
        let expires = "expires=" + d.toUTCString();
        document.cookie = this.cookie_name + '=' + consent + "; " + expires + ";path=/";

        $(document).trigger("user_cookie_consent_changed", {'consent' : consent});
    };

    // Let's see if we have a consent cookie already
    private userAlreadyAcceptedCookies() {
        let userAcceptedCookies:Boolean|string = false;
        let cookies = document.cookie.split(";");
        for (let i = 0; i < cookies.length; i++) {
            let c = cookies[i].trim();
            if (c.indexOf(this.cookie_name) == 0) {
                userAcceptedCookies = c.substring(this.cookie_name.length + 1, c.length);
            }
        }

        return userAcceptedCookies;
    };

    private hideContainer() {
        // $('.eupopup-container').slideUp(200);
        $('.eupopup-container').animate({
            opacity: 0,
            height: 0
        }, 200, function() {
            $('.eupopup-container').hide(0);
        });
    };

    private publicfunc() {

        if (this.userAlreadyAcceptedCookies()) {
            return;
        }

        // We should initialise only once
        if (this.initialised) {
            return;
        }

        this.initialised = true;

        // Markup and event listeners >>>
        this.html_markup = this.createHtmlMarkup();

        if ($('.eupopup-container').length > 0) {
            //$('.eupopup-container').append(this.html_markup);
        } else {
            $('body').append(this.html_markup);
        }

        $('.eupopup-button_1').click(() => {
            this.setUserAcceptsCookies(true);
            this.hideContainer();
            return false;
        });
        $('.eupopup-closebutton').click(() => {
            this.setUserAcceptsCookies(true);
            this.hideContainer();
            return false;
        });
        // ^^^ Markup and event listeners

        // Ready to start!
        $('.eupopup-container').show();

        // In case it's alright to just display the message once
        if (this.options.autoAcceptCookiePolicy) {
            this.setUserAcceptsCookies(true);
        }
    };


}

/**
 * Default cookie law accept options
 */
interface CookieLawAcceptOptions {
    cookiePolicyUrl?:String,
    popupPosition?:String,
    colorStyle?:String,
    compactStyle?:Boolean,
    popupTitle?:String,
    popupText?:String,
    buttonContinueTitle?:String,
    buttonLearnmoreTitle?:String,
    buttonLearnmoreOpenInNewWindow?:Boolean,
    agreementExpiresInDays?:number,
    autoAcceptCookiePolicy?:Boolean,
    htmlMarkup?:String|null
}