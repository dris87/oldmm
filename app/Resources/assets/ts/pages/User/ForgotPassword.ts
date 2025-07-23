import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Modal } from '../../components/molecules/Modal';
import {Framework} from "../../libs/Framework";

export default class UserForgotPassword{

    public constructor(){
    
        // Define firm colleague form and it's callbacks
        let forgotPasswordForm = new Form( $("#send-forgot-password-message") , {} , false , $('#reset-password-form-container'));
        forgotPasswordForm.addValidationSuccessEvent( ( data ) => {
            new Modal( $('#forgot-password-success-modal') , {} , true);
            forgotPasswordForm.getElement().find('input[name="_token"]').val( data.token );
            forgotPasswordForm.clear();
        });
        forgotPasswordForm.addErrorEvent(()=>{
            Framework.throwErrorModal();
        });

    }
    
}