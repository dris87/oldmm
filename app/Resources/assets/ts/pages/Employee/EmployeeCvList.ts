import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Modal } from '../../components/molecules/Modal';
import { Request } from '../../libs/util/Request';
import { Framework } from '../../libs/Framework';
import Notification from '../../components/atoms/Notification';

/**
 * Employee Cv List Page
 */
export default class EmployeeCvList{

    /**
     *
     */
    public constructor(){

        this.initStatusChangeEvent();

        this.initCustomizeModal();

    }

    /**
     *
     */
    private initCustomizeModal(){
        let customizeModal:Modal = new Modal( $("#emplyoee-cv-customize-modal") );

        $(".m-employee-card").on('click','.m-employee-card--customize-button', ()=>{
            customizeModal.show();
        });
    }

    /**
     * On status change event
     */
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

        $("#employee-cv-list").on('click' , '.m-switch', function(e){
            request.setOptionValue( 'url' , $(this).data('action') );
            request.submit();
        });
    }
    
}