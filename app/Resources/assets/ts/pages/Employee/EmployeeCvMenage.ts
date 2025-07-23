import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Steps } from '../../components/organisms/Steps';
import LeavePageAction from '../../libs/behavioural/LeavePageAction';
import { Template } from '../../libs/util/Template';
import TimelineForm, { TimelineFormElements, TimelineCallbacks, TimelineRequestUrls } from '../../libs/util/TimelineForm';
import {Util} from "../../libs/util/Util";

export default class EmployeeCvMenage{

    /**
     * Holds our steps instance
     */
    private steps:Steps;

    private cvExtraForm:Form;
    private cvOtherForm:Form;
    private educationForm:Form;
    private experienceForm:Form;

    /**
     * Step number by its name ;)
     */
    private stepNumbers: any = {
        workDetail : 0,
        extra : 1,
        education : 2,
        experience : 3,
        other : 4,
        documents : 5
    };

    /**
     * Initializez the steps
     */
    public constructor(){

        this.steps = new Steps( $('#employee-steps-menage') , {
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
        this.initWorkDetailsStep();
        this.initExtraStep();
        this.initEducationStep();
        this.initExperienceStep();
        this.initOtherStep();

        this.initElementBehaviour();
    }

    /**
     * #2 step - Work Details
     *
     * Creates/Updates an employee cv
     */
    private initWorkDetailsStep(){
        let cvDetailsForm = new Form( $("#employee-cv-work-details-form") );
        cvDetailsForm.addValidationSuccessEvent( ( data ) =>{
            this.cvExtraForm.setOptionValue('url',this.cvExtraForm.getAction().replace('/0','/' + data.id));
            this.cvOtherForm.setOptionValue('url',this.cvOtherForm.getAction().replace('/0','/' + data.id));
            this.educationForm.setOptionValue('url',this.educationForm.getAction().replace('/0','/' + data.id));
            this.experienceForm.setOptionValue('url',this.experienceForm.getAction().replace('/0','/' + data.id));
            $('#employee-steps-menage').removeAttr('data-new');
            this.steps.enableForceLeave();
        });

        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.workDetail , ()=>{
            if( $('#employee-steps-menage').attr('data-new') == 'yes' ) {
                cvDetailsForm.submit();
                return true;
            }else {
                this.steps.enableForceLeave();
            }
        });
    }

    /**
     * #3 step - Extra information
     *
     * Updates a new employee cv
     */
    private initExtraStep(){
        this.cvExtraForm = new Form( $("#employee-cv-extra-form") );
        this.cvExtraForm.addValidationSuccessEvent( ( data ) =>{
            //this.steps.next();
            this.steps.enableForceLeave();
        });
        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.extra , ()=>{
            //cvExtraForm.submit();
            //return true;
            this.steps.enableForceLeave();
        });
    }

    private initOtherStep(){
        this.cvOtherForm = new Form( $("#employee-cv-other-form") );
        this.cvOtherForm.addValidationSuccessEvent( ( data ) =>{
            this.steps.enableForceLeave();
        });
        // Add an event when we want to leave the nullth step
        this.steps.addOnLeaveCallback( this.stepNumbers.extra , ()=>{
            this.steps.enableForceLeave();
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

        this.educationForm = new Form( $("#employee-cv-education-form") );
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

                if( noTimeline ){
                    this.steps.showButton('next');
                }else{
                    this.steps.hideButton('next');
                }

            },
            initElementBehaviourCallback: ( self: TimelineForm ) => {

                $inProgress.on('change', ()=>{
                    let isChecked = $inProgress.is(':checked');
                    $toDate.prop('disabled', isChecked ? 'disabled' : '');
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
            },
            onShowTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.hide();
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
            this.educationForm,
            timelineFormElements,
            timelineFormCallbacks ,
            timelineRequestUrls,
            'fromDate'
        );
        timelineForm.initData(educationData);

        this.steps.addOnLeaveCallback( this.stepNumbers.education , ()=>{
            if( timelineForm.isReady() )
                this.steps.enableForceLeave();
        },'forward');
        this.steps.addOnLeaveCallback( this.stepNumbers.education , ()=>{
            if( timelineForm.isReady() )
                this.steps.enableForceLeave();
        },'backward');

        $newEducation.on('click',()=>{
            $toDate.prop('disabled', '' );
        });

    }

    /**
     * #4 step - Experience
     *
     * Creates/Removes/Updates experiences
     */
    private initExperienceStep(){

        let $inProgress:JQuery<HTMLElement> = $("#cv_experience_inProgress");
        let $toDate:JQuery<HTMLElement> = $("#cv_experience_toDate");
        let $newExperience:JQuery<HTMLElement> = $("#employee-cv-experience-new");

        this.experienceForm = new Form( $("#employee-cv-experience-form") );
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
            },
            onShowTimelineContainerCallback: ( data:object, elements:TimelineFormElements ) => {
                elements.noTimelineContainer.hide();
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
            this.experienceForm,
            timelineFormElements,
            timelineFormCallbacks ,
            timelineRequestUrls,
            'fromDate'
        );
        timelineForm.initData(experienceData);

        this.steps.addOnLeaveCallback( this.stepNumbers.experience , ()=>{
            if( timelineForm.isReady() )
                this.steps.enableForceLeave();
        },'forward');
        this.steps.addOnLeaveCallback( this.stepNumbers.experience , ()=>{
            if( timelineForm.isReady() )
                this.steps.enableForceLeave();
        },'backward');

        $newExperience.on('click',()=>{
            $toDate.prop('disabled', '' );
        });

    }

    /**
     * This method will initialize the behaviour on the page
     */
    private initElementBehaviour(){

        new LeavePageAction();

        // Toggle the display of the WillToMove panel by the checkbox
        let willToMoveToggler = $("#cv-details-form-toggle-will-to-move-panel-toggler"),
            willToMovePanel = $("#cv-details-form-toggle-will-to-move-panel");

        if( willToMoveToggler.is(':checked') ){
            willToMovePanel.show();
        }

        if( willToMovePanel.find('input[type=radio]').is(':checked') ){
            willToMoveToggler.prop('checked', true);
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