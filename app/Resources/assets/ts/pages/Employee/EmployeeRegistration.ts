import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Steps } from '../../components/organisms/Steps';
import { Framework } from '../../libs/Framework';
import LeavePageAction from '../../libs/behavioural/LeavePageAction';
import { Util } from '../../libs/util/Util';
import { Request } from '../../libs/util/Request';
import { Modal } from '../../components/molecules/Modal';
import { SystemConfig } from '../../libs/SystemConfig';
import { Template } from '../../libs/util/Template';
import TimelineForm, { TimelineFormElements, TimelineCallbacks, TimelineRequestUrls } from '../../libs/util/TimelineForm';
import Upload from "../../components/atoms/Upload";

export default class EmployeeRegistration{

    /**
     * Holds all of our educations
     * Ordered by start from
     */
    private educations:any = {};

    /**
     * Holds our steps instance
     */
    private steps:Steps;

    /**
     * Step number by its name ;)
     */
    private stepNumbers: any = {
        employee : 0,
        workDetail : 1,
        extra : 2,
        education : 3,
        experience : 4,
        other : 5,
        documents : 6, 
        success : 7
    };

    /**
     * Initializez the steps
     */
    public constructor(){

        this.steps = new Steps( $('#employee-steps-registration') );
        this.initUserStep();
        this.initWorkDetailsStep();
        this.initExtraStep();
        this.initOtherStep();
        this.initEducationStep();
        this.initExperienceStep();

        this.initElementBehaviour();
    }

    /**
     * #1 step - Personal Details
     * Create the user
     */
    private initUserStep(){
        // Define employee form and it's callbacks
        let employeeForm = new Form( $("#employee-form") );
        employeeForm.addValidationSuccessEvent( ( data ) =>{
            $(".firm-registration-user-step-title").hide();
            $(".firm-registration-details-step-title").show();
            this.steps.next();
        });

        // Add an event when we want to leave the null-th step
        this.steps.addOnLeaveCallback( this.stepNumbers.employee , ()=>{
            employeeForm.submit();
            return true;
        });
    }

    /** 
     * #2 step - Work Details
     * 
     * Creates/Updates an employee cv
    */
    private initWorkDetailsStep(){
        let cvDetailsForm = new Form( $("#employee-cv-work-details-form") );
        cvDetailsForm.addValidationSuccessEvent( ( data ) =>{
            $(".firm-registration-details-step-title").hide();
            $(".firm-registration-extra-step-title").show();
            this.steps.next();
        });

        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.workDetail , ()=>{
            cvDetailsForm.submit();
            return true;
        });
    }

    /** 
     * #3 step - Extra information
     * 
     * Updates a new employee cv
    */
    private initExtraStep(){
        let cvExtraForm = new Form( $("#employee-cv-extra-form") );
        cvExtraForm.addValidationSuccessEvent( ( data ) =>{
            $(".firm-registration-extra-step-title").hide();
            $(".firm-registration-education-step-title").show();
            this.steps.next();
            //this.steps.hideButton('next');
        });
        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.extra , ()=>{
            cvExtraForm.submit();
            return true;
        });
    }
    /**
     * #6  step - Other information
     *
     * Updates a new employee cv
    */
    private initOtherStep(){
        let cvOtherForm = new Form( $("#employee-cv-other-form") );
        cvOtherForm.addValidationSuccessEvent( ( data ) =>{
            $(".firm-registration-experience-step-title").hide();
            $(".firm-registration-other-step-title").hide();
            $(".firm-registration-success-step-title").show();
            this.steps.next();
        });
        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.other , ()=>{
            cvOtherForm.submit();
            return true;
        });
    }

    /** 
     * #4 step - Educations
     * 
     * Creates/Removes/Updates educations
    */
    private initEducationStep(){

        let $inProgress:JQuery<HTMLElement> = $("#cv_education_inProgress");
        let $toDate:JQuery<HTMLElement> = $("#cv_education_toDate");
        let $newEducation:JQuery<HTMLElement> = $("#employee-cv-education-new");

        let educationForm = new Form( $("#employee-cv-education-form") );
        let $timelineContainer:JQuery<HTMLElement> = $("#employee-education-timeline-container");
        let timelineFormElements: TimelineFormElements = {
            new: $newEducation,
            cancel: $("#employee-cv-education-back"),
            noTimeline: $('#employee-cv-education-no--education'),
            noTimelineContainer: $("#employee-cv-education-no--education-container"),
            confirmDeleteModal: $('#confirm-education-deletion-modal'),
            timelineContainer: $timelineContainer,
            timelineItemTemplate: "#employee-education-timeline-item-template"
        };

        let educationData = $timelineContainer.data('educations'),
            educationEditAction = $timelineContainer.data('action-edit'),
            educationDeleteAction = $timelineContainer.data('action-delete');
        let timelineFormCallbacks: TimelineCallbacks = {
            onFormShowCallback: () => {},
            onNoTimelineChangeCallback: ( noTimeline:boolean ) => {

                // if( noTimeline ){
                   //  this.steps.showButton('next');
                // }else{
                //     this.steps.hideButton('next');
                // } 

            },
            initElementBehaviourCallback: ( self: TimelineForm ) => {

                $inProgress.on('change', ()=>{
                    let isChecked = $inProgress.is(':checked');
                    $toDate.prop('disabled', isChecked ? 'disabled' : '' );
                    if( isChecked ) {
                        $toDate.val(null);
                    }
                });

                //this.steps.hideButton('next');
            },
            renderTimelineItemTransformCallback: ( item:any ) => {  return {
                'sub-title' : item['educationLevel']['_labels'][0],
                'title'     : item['school']['_labels'][0],
                'date'      : item['location']['_labels'][0] + ', ' + item['fromDate'] + ' - ' + ( ( item['toDate'] != undefined ) ? item['toDate'] : '' ),
                'comment'   : item['comment'],
                'id'        : "cv_education-" + item['id']
            } },
            onHideTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.show();
                this.steps.hideButton('next');
            },
            onShowTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.hide();
                this.steps.showButton('next');
            },
            onFormSuccessCallback: ( data:object, self: TimelineForm , isModification: boolean ) => {
                $toDate.removeAttr('disabled');
            },
            onEditButtonClickCallback: ( self: TimelineForm ) => {},
            onDeleteSuccessCallback: ( self: TimelineForm ) => {}
        };

        let timelineRequestUrls:TimelineRequestUrls = {
            delete: educationDeleteAction.replace('/0',''),
            edit: educationEditAction.replace('/0',''),
            inputName: 'cv_education'
        };

        let timelineForm: TimelineForm = new TimelineForm(
            educationForm,
            timelineFormElements,
            timelineFormCallbacks ,
            timelineRequestUrls,
            'fromDate'
        );
        timelineForm.initData(educationData);

        this.steps.addOnLeaveCallback( this.stepNumbers.education , ()=>{
            //
            //if( timelineForm.isReady() ) {
                this.steps.enableForceLeave();
                $(".firm-registration-education-step-title").hide();
                $(".firm-registration-experience-step-title").show();
                this.steps.hideButton('next');
            //}
        },'forward');

        $newEducation.on('click',()=>{
            $toDate.prop('disabled', '' );
        });

    }

    private initExperienceStep(){
        let $inProgress:JQuery<HTMLElement> = $("#cv_experience_inProgress");
        let $toDate:JQuery<HTMLElement> = $("#cv_experience_toDate");
        let $newExperience:JQuery<HTMLElement> = $("#employee-cv-experience-new");

        let experienceForm = new Form( $("#employee-cv-experience-form") );
        let $timelineContainer:JQuery<HTMLElement> = $("#employee-experience-timeline-container");
        let timelineFormElements: TimelineFormElements = {
            new: $newExperience,
            cancel: $("#employee-cv-experience-back"),
            noTimeline: $('#employee-cv-experience-no--experience'),
            noTimelineContainer: $("#employee-cv-experience-no--experience-container"),
            confirmDeleteModal: $('#confirm-experience-deletion-modal'),
            timelineContainer: $timelineContainer,
            timelineItemTemplate: "#employee-experience-timeline-item-template"
        };

        let experienceData = $timelineContainer.data('experiences'),
            experienceEditAction = $timelineContainer.data('action-edit'),
            experienceDeleteAction = $timelineContainer.data('action-delete');
        let timelineFormCallbacks: TimelineCallbacks = {
            onFormShowCallback: () => {},
            onNoTimelineChangeCallback: ( noTimeline:boolean ) => {

                if( noTimeline ){
                    this.steps.showButton('next');
                }else{
                    this.steps.hideButton('next');
                }

            },
            initElementBehaviourCallback: ( self: TimelineForm ) => {

                $inProgress.on('change', ()=>{
                    let isChecked = $inProgress.is(':checked');
                    $toDate.prop('disabled', isChecked ? 'disabled' : '' );
                    if( isChecked ) {
                        $toDate.val(null);
                    }
                });
            },
            renderTimelineItemTransformCallback: ( item:any ) => {  return {
                'sub-title' : item['companyName'],
                'title'     : item['experience']['_labels'][0],
                'date'      : item['location']['_labels'][0] + ', ' + item['fromDate'] + ' - ' + ( ( item['toDate'] != undefined ) ? item['toDate'] : '' ),
                'comment'   : item['comment'],
                'id'        : "cv_experience-" + item['id']
            } },
            onHideTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.show();
                this.steps.hideButton('next');
            },
            onShowTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.hide();
                this.steps.showButton('next');
            },
            onFormSuccessCallback: ( data:object, self: TimelineForm , isModification: boolean ) => {
                $toDate.removeAttr('disabled');
            },
            onEditButtonClickCallback: ( self: TimelineForm ) => {},
            onDeleteSuccessCallback: ( self: TimelineForm ) => {}
        };

        let timelineRequestUrls:TimelineRequestUrls = {
            delete: experienceDeleteAction.replace('/0',''),
            edit: experienceEditAction.replace('/0',''),
            inputName: 'cv_experience'
        };

        let timelineForm: TimelineForm = new TimelineForm(
            experienceForm,
            timelineFormElements,
            timelineFormCallbacks ,
            timelineRequestUrls,
            'fromDate'
        );
        timelineForm.initData(experienceData);

        this.steps.addOnLeaveCallback( this.stepNumbers.experience , ()=>{
            if( timelineForm.isReady() ) {
                this.steps.enableForceLeave();
                $(".firm-registration-experience-step-title").hide();
                $(".firm-registration-other-step-title").show();
            }
        },'forward');

        $newExperience.on('click',()=>{
            $toDate.prop('disabled', '' );
        });
    }

    /** 
     * This method will initialize the behaviour on the page
    */
    private initElementBehaviour(){

        new LeavePageAction();

        let uploader = new Upload();

        this.steps.showButton('next');

        // Toggle the display of the WillToMove panel by the checkbox
        let willToMoveToggler = $("#cv-details-form-toggle-will-to-move-panel-toggler"),
            willToMovePanel = $("#cv-details-form-toggle-will-to-move-panel");

        if( willToMoveToggler.is(':checked') ){
            willToMovePanel.show();
        }

        willToMoveToggler.on('click', ()=>{
            willToMovePanel.toggle();

            if( !willToMoveToggler.is(':checked') ){
                let $radio = willToMovePanel.find('input[type="radio"]');
                $radio.prop('checked',false);
            }
        } );

    }
}