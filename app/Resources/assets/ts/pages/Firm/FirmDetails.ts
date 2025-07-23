import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Steps } from '../../components/organisms/Steps';
import { Framework } from '../../libs/Framework';
import Notification from "../../components/atoms/Notification";
import LeavePageAction from "../../libs/behavioural/LeavePageAction";
import Upload from "../../components/atoms/Upload";

export default class FirmDetails{

    /**
     * Step number by its name ;)
     */
    private stepNumbers: any = {
        invoice : 0,
        info : 1,
        introduction : 2
    };

    /**
     * Holds the steps lib instance
     */
    private steps: Steps;

    /**
     * Holds the form instance for our firm colleague form
     */
    private firmDetailsForm: Form;

    public constructor(){

        let uploader = new Upload();

        // Define firm colleague form and it's callbacks
        this.firmDetailsForm = new Form( $("#firm-details-form") );
        this.firmDetailsForm.addValidationSuccessEvent( ( data ) => {
            new Notification( {
                title: data.title,
                message: data.message
            });
        });

        this.steps = new Steps( $('#firm-steps-details') , {
            theme: 'default',
            cycleSteps: true,
            useURLhash: false,
            transitionEffect: 'fade',
            toolbarSettings: {
                toolbarPosition: 'none', // none, top, bottom, both
            },
            anchorSettings: {
                anchorClickable: true,
                enableAllAnchors: true,
                markDoneStep: true,
                markAllPreviousStepsAsDone: true,
                removeDoneStepOnNavigateBack: false,
                enableAnchorOnDoneStep: true
            },
            autoAdjustHeight: true,
            selected: 0,

        } );
        this.initFirmInvoiceStep();
        this.initFirmInfoStep();
        this.initFirmIntroductionStep();
        this.initElementBehaviour();

    }

    /**
     *
     */
    private initFirmInvoiceStep(){

        this.steps.addOnLeaveCallback( this.stepNumbers.invoice , ()=>{
            this.steps.enableForceLeave();
        });

    }

    /**
     *
     */
    private initFirmInfoStep(){

        this.steps.addOnLeaveCallback( this.stepNumbers.info , ()=>{
            this.steps.enableForceLeave();
        });
    }

    /**
     *
     */
    private initFirmIntroductionStep(){

        this.steps.addOnLeaveCallback( this.stepNumbers.introduction , ()=>{
            this.steps.enableForceLeave();
        });
    }

    /**
     * This method will initialize the behaviour on the page
     */
    private initElementBehaviour(){

        // Toggle the display of the WillToMove panel by the checkbox
        let willToMoveToggler = $("#firm-details-postal-address-toggler"),
            willToMovePanel = $("#firm-details-postal-address-panel");

        if( willToMoveToggler.is(':checked') ){
            willToMovePanel.show();
        }

        willToMoveToggler.on('click', ()=>{
            willToMovePanel.toggle();
        } );

    }

}