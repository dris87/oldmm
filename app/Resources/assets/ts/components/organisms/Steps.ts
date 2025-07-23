/// <reference path="../../types/smartwizard.d.ts"/>
import * as $ from 'jquery';
import '../../../libraries/smartwizard/dist/js/jquery.smartWizard';
/**
 * This class will handle step based stuff
 * 
 * We use smartwizard jquery plugin
 * @documentation http://techlaboratory.net/smartwizard/documentation
 */
export class Steps{

    /**
     * Development options, No step limitations
     */
    public static devOptions:smartWizardOptions = {
        useURLhash: true,
        anchorSettings: {
            anchorClickable: true,
            enableAllAnchors: true,
            markDoneStep: true,
            markAllPreviousStepsAsDone: true,
            removeDoneStepOnNavigateBack: false,
            enableAnchorOnDoneStep: true
        }
    };

    /**
     * Any default ajax options here
     * @type {{}}
     */
    private options:smartWizardOptions = {
        theme: 'circles',
        lang: { 
            next: 'Következő',
            previous: 'Vissza'
        },
        useURLhash: false,
        transitionEffect: 'fade',
        toolbarSettings: {
            toolbarPosition: 'bottom', // none, top, bottom, both
            toolbarButtonPosition: '', // left, right
            showNextButton: true, // show/hide a Next button
            showPreviousButton: false, // show/hide a Previous button
        },
        anchorSettings: {
            anchorClickable: false,
            enableAllAnchors: false,
            markDoneStep: false,
            markAllPreviousStepsAsDone: false,
            removeDoneStepOnNavigateBack: false,
            enableAnchorOnDoneStep: true
        },
        autoAdjustHeight: true,
        selected: 0,
    };

    /**
     * This boolean will prevent stepping back
     */
    public preventBackStep: boolean = false;

    /**
     * 
     */
    public callbacks: any = {};

    /**
     * We use this to make sure we will step to the next page
     */
    private forceLeave: boolean = false;

    /**
     *
     * @param {JQuery<HTMLElement>} $element
     * @param {smartWizardOptions} options
     * @param {boolean} showByDefault
     */
    public constructor( private $element:JQuery<HTMLElement>, options?:smartWizardOptions , showByDefault:boolean = false){

        this.options = (<any>Object).assign( this.options , options );

        this.$element = $element.smartWizard( this.options );

        let $container = this.$element.parents('.m-steps--hidden');
        $container.removeClass('m-steps--hidden');
        $container.find('.m-steps--loader').remove();
        $(window).scrollTo(0, 0);

        // Initialize the leaveStep event
        this.$element.on("leaveStep", (e, anchorObject:JQuery<HTMLElement>, stepNumber: number, stepDirection: string) => {
            if( this.forceLeave ) {
                this.forceLeave = false;
                return true;
            }

            if( stepDirection.toLowerCase() !== 'forward' && this.preventBackStep ){
                // console.log("Firm registration smartwizard leave step: you cant leave this step");
                return false;
            }

            if( typeof this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-leave' ] === 'function' ){
                
                this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-leave' ].call( this, e, anchorObject, stepNumber, stepDirection );

                if( !this.forceLeave )
                    return false;
            }
                
        });

        // Initialize the leaveStep event
        this.$element.on("showStep", (e, anchorObject:JQuery<HTMLElement>, stepNumber: number, stepDirection: string) => {

            if( typeof this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-show' ] === 'function' ){
                
                this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-show' ].call( this, e, anchorObject, stepNumber, stepDirection );
                
            }
                
        });

    }

    /**
     * Triggers when leaving a step.
     * This is a decision making event. 
     * based on its function return value (true/false) 
     * the current step navigation can be cancelled.
     * 
     * @param stepNumber 
     * @param callback 
     * @param stepDirection 
     */
    public addOnLeaveCallback( 
        stepNumber:number, 
        callback:( e:object, anchorObject:JQuery<HTMLElement>, stepNumber: number, stepDirection: string)=>void, 
        stepDirection = 'forward' )
    {
        this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-leave' ] = callback;
    }
    /**
     * Triggers when showing a step.
     * 
     * @param stepNumber 
     * @param callback 
     * @param stepDirection 
     */
    public addOnShowCallback( 
        stepNumber:number, 
        callback:( e:object, anchorObject:JQuery<HTMLElement>, stepNumber: number, stepDirection: string)=>void, 
        stepDirection = 'forward' )
    {
        this.callbacks[ stepDirection.toString() + '-' + stepNumber.toString() + '-show' ] = callback;
    }

    /**
     * Step to the next page
     */
    public next(){
        $(window).scrollTo(0, 0);
        this.enableForceLeave();
        this.$element.smartWizard('next');

    }

    /**
     *
     */
    public enableForceLeave(){
        this.forceLeave = true;
    }

    /**
     * Step to the previous page
     */
    public prev(){

        this.enableForceLeave();
        this.$element.smartWizard('previous');

    }

    public fixSize(){

        this.$element.find('.sw-container').removeAttr('style');

    }

    public getElement():JQuery<HTMLElement>{
        return this.$element;
    }

    public hideButton( button:string = 'next' ){
        this.$element.find('.sw-btn-' + button ).hide();
    }

    public showButton( button:string = 'next' ){
        this.$element.find('.sw-btn-' + button ).show();
    }

    public toggleButton( button:string = 'next' ){
        this.$element.find('.sw-btn-' + button ).toggle();
    }


}