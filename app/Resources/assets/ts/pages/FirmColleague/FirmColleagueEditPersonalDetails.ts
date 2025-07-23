import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Modal } from '../../components/molecules/Modal';
import { Request } from '../../libs/util/Request';
import { Framework } from '../../libs/Framework';
import Notification from '../../components/atoms/Notification';
import Upload from "../../components/atoms/Upload";

export default class FirmColleagueEditPersonalDetails{

    public constructor(){
    
        // Define firm colleague form and it's callbacks
        let firmColleagueEditPersonalDetailsForm = new Form( $("#firm-colleague-details-form") );
        firmColleagueEditPersonalDetailsForm.addValidationSuccessEvent( ( data ) => {
            new Notification( {
                title: data.title,
                message: data.message
            });
            firmColleagueEditPersonalDetailsForm.getElement().find('input[name="_token"]').val( data.token );
        });

        let firmColleagueChangePasswordForm = new Form( $("#change-password-form") );
        firmColleagueChangePasswordForm.addValidationSuccessEvent( ( data ) => {
            new Notification( {
                title: data.notification.title,
                message: data.notification.message
            });
            firmColleagueChangePasswordForm.clear();
        });

        this.initStatusChangeEvent();

        this.initFirmColleagueDeleteEvent();

    }

    private initStatusChangeEvent(){
        let request = new Request( {
            method: 'POST'
        } ),
            self = this;

        request.addSuccessEvent( ( data:any )=>{
            if( data.success == 1 ) {
                new Notification( {
                    title: data.title,
                    message: data.message
                });
            }else{
                Framework.throwErrorModal();
            }
        } );

        $("#firm-colleague-details-form").on('click' , '.m-switch', function(e){
            request.setOptionValue( 'url' , $(this).data('action') );
            request.submit();
        });
    }

    private initFirmColleagueDeleteEvent(){
        let firmColleagueDeleteModal = $('#firm-colleague-account-delete-confirmation-modal'),
            firmColleagueDeleteModalTrigger = $("#firm-colleague-delete-account-modal-trigger");

        let ModalInstance:Modal = new Modal( firmColleagueDeleteModal , {
            show: false
        });

        let firmColleagueDeleteForm = new Form( firmColleagueDeleteModal.find( '#firm-colleague-account-delete-form' ) , {}, false);

        firmColleagueDeleteForm.addErrorEvent( ( jqXHR, textStatus:any, errorThrown ) => {
            Framework.throwErrorModal();
        } );

        firmColleagueDeleteModalTrigger.on('click', ( e )=>{
            e.preventDefault();
            e.stopPropagation();
            ModalInstance.show();
        } );
    }
    
}