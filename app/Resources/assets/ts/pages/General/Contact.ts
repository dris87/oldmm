import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Modal } from '../../components/molecules/Modal';

export default class GeneralContact{

    public constructor(){
    
        // Define firm colleague form and it's callbacks
        let contactForm = new Form( $("#send-contact-message") );
        contactForm.addErrorEvent( () => {});
        contactForm.addValidationSuccessEvent( ( data ) => { 
            new Modal( $('#contact-success-modal') , {} , true);
            contactForm.getElement().find('input[name="_token"]').val( data.token );
            contactForm.clear();
        });
        contactForm.addCompleteEvent( () => {} );

    }
    
}