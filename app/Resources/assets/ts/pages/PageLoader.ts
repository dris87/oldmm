import { Util } from "../libs/util/Util";
import EmployeeRegistration from "./Employee/EmployeeRegistration";
import UserResetPassword from "./User/ResetPassword";
import UserForgotPassword from "./User/ForgotPassword";
import OfferList from "./Offer/List";
import OfferShow from "./Offer/Show";
import FirmRegistration from "./Firm/FirmRegistration";
import FirmOfferCreate from "./Firm/Offer/FirmOfferCreate";
import FirmColleagueLogin from "./FirmColleague/FirmColleagueLogin";
import GeneralContact from "./General/Contact";
import FirmPurchaseServices from "./Firm/Purchase/FirmPurchaseService";
import FirmPurchaseCart from "./Firm/Purchase/FirmPurchaseCart";
import FirmOfferList from "./Firm/Offer/FirmOfferList";
import FirmColleagueEditPersonalDetails from "./FirmColleague/FirmColleagueEditPersonalDetails";
import EmployeeEditPersonalDetails from "./Employee/EmployeeEditPersonalDetails";
import EmployeeCvList from "./Employee/EmployeeCvList";
import EmployeeCvMenage from "./Employee/EmployeeCvMenage";
import FirmDetails from "./Firm/FirmDetails";
import * as $ from "jquery";
import FirmCandidateList from "./Firm/FirmCandidateList";
import FirmDatabaseAccess from "./Firm/FirmDatabaseAccess";

/**
 * Page boot loader class
 * This will make sure that if the current page
 * has an associated ts class, it will load only that
 */ 
export default class PageLoader{

    /**
     * Instance of current class object
     */
    private currentPageClass:any;

    /**
     * current page Id
     */
    private currentPageId:string;

    /**
     *
     */
    public constructor(){
        this.currentPageId = $("body").prop('id');
    }

    /**
     * Inits the current page.
     */
    public initCurrentPage(){
        this.loadPage( PageLoader.convertPageIdToTsClassPath( this.currentPageId ) );
    }

    /**
     * @returns {any}
     */
    public getCurrentPage(){
        return this.currentPageClass;
    }

    /**
     * @returns {string}
     */
    public getCurrentPageId(){
        return this.currentPageId;
    }

    /**
     * This will init a specific page
     * @param tsClassName 
     */
    public loadPage( tsClassName:string ){

        switch( tsClassName ){

            // Employee Classes
            case 'EmployeeRegistration':
                this.currentPageClass = new EmployeeRegistration();
            break;
            case 'EmployeeCvMenage':
                this.currentPageClass = new EmployeeCvMenage();
            break;

            // Firm Classes
            case 'FirmRegistration':
                this.currentPageClass = new FirmRegistration();
            break;
            case 'FirmPurchaseService':
                this.currentPageClass = new FirmPurchaseServices();
            break;
            case 'FirmPurchaseCart':
                this.currentPageClass = new FirmPurchaseCart();
            break;
            case 'FirmDetails':
                this.currentPageClass = new FirmDetails();
            break;
            case 'FirmCandidateList':
                this.currentPageClass = new FirmCandidateList();
            break;
            case 'FirmDatabaseAccess':
                this.currentPageClass = new FirmDatabaseAccess();
            break;

                // Firm Offer List Classes
                case 'FirmOfferList':
                    this.currentPageClass = new FirmOfferList();
                break;
                // Firm Offer Classes
                case 'FirmOfferCreate':
                    this.currentPageClass = new FirmOfferCreate();
                break;
            // Firm colleague Classes

            case 'FirmColleagueLogin':
                this.currentPageClass = new FirmColleagueLogin();
            break;

            case 'FirmColleagueEditPersonalDetails':
                this.currentPageClass = new FirmColleagueEditPersonalDetails();
            break;

            case 'EmployeeEditPersonalDetails':
                this.currentPageClass = new EmployeeEditPersonalDetails();
            break;
    
            case 'EmployeeCvList':
                this.currentPageClass = new EmployeeCvList();
            break;

            // Offer Classes
            case 'OfferList':
                this.currentPageClass = new OfferList();
            break;
            case 'OfferShow':
                this.currentPageClass = new OfferShow();
            break;

            // User Classes
            case 'UserResetPassword':
                this.currentPageClass = new UserResetPassword();
            break;
            case 'UserForgotPassword':
                this.currentPageClass = new UserForgotPassword();
            break;

            // General classes
            
            case 'GeneralContact':
                this.currentPageClass = new GeneralContact();
            break;
        
        }

    }

    /**
     * The pageId will come in this format: employee-registration
     * The ts class path needs to be in camel case: Employee/EmployeeRegistration
     * @param pageId 
     */
    private static convertPageIdToTsClassPath( pageId:string ){

        let find = /(\-\w)/g;
        let convert = function( matches:any ){
            return matches[1].toUpperCase();
        };
        let camelCaseString = pageId.replace(
            find,
            convert
        );

        return Util.ucfirst( camelCaseString );

    }

}
