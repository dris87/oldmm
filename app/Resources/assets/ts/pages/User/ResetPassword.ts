import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Modal } from '../../components/molecules/Modal';
import {Framework} from "../../libs/Framework";

export default class UserResetPassword{

    public constructor(){
    
        // Define firm colleague form and it's callbacks
        let resetPasswordForm = new Form( $("#reset-password-message"), {}, false, $('#reset-password-form-container') );
        resetPasswordForm.addValidationSuccessEvent( ( data ) => { 
            new Modal( $('#reset-password-success-modal') , {} , true);
            resetPasswordForm.clear();
            resetPasswordForm.disable();
            $("#reset-password-form-success").show();
            $("#reset-password-form-container").hide();
        });

        resetPasswordForm.addErrorEvent(()=>{
            Framework.throwErrorModal();
        });

    }
}