import * as $ from 'jquery';
import { Form } from '../../libs/util/Form';
import { Framework } from '../../libs/Framework';
import {Request} from "../../libs/util/Request";
import {Util} from "../../libs/util/Util";

export default class FirmRegistration{

    /**
     * @type {string}
     */
    private firmRegistrationFormSelector = '#firm-registration-form';

    /**
     * @type {string}
     */
    private taxNumberInputSelector = "#form_firm_taxNumber";

    /**
     *
     * @type {string}
     */
    private loadFirmInfoButtonSelector = "#firm-form-load-firm-details-by-tax-number";

    /**
     * Holds the form instance for our firm colleague form
     */
    private firmRegistrationForm: Form;

    public constructor(){
        this.firmRegistrationForm = new Form( $(this.firmRegistrationFormSelector) );
        this.firmRegistrationForm.addValidationSuccessEvent( ( data ) => {
            if( typeof data.user_id != undefined ){
                return;
            }
            Framework.throwErrorModal();
        });

        this.initLoadFirmInfoEvent();
    }

    /**
     *
     */
    private initLoadFirmInfoEvent(){

        let $loadFirmInfo = $(this.loadFirmInfoButtonSelector),
            $taxNumberInput = $(this.taxNumberInputSelector);

        $taxNumberInput.on('keyup', function () {
            Util.disableElement($loadFirmInfo,!( parseInt( $taxNumberInput.val().toString() ).toString().length > 10 ));
        });

        Util.disableElement($loadFirmInfo,true);

        let request = new Request( {
                method: 'POST'
            } ),
            self = this;

        request.addSuccessEvent( ( data:any )=>{
            if( data.success == 1 ) {
                this.loadFirmInfoByTaxNumber( data.firmData );
            }else{
                Framework.throwErrorModal();
            }
        } );
        request.addCompleteEvent(()=>{
            self.firmRegistrationForm.getLoader().stop();
        });

        $loadFirmInfo.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let taxNumber = $taxNumberInput.val();
            if( parseInt( taxNumber.toString() ).toString().length > 10 ) {
                request.setOptionValue('url', $(this).attr('data-action').replace('/0', '/' + taxNumber));
                request.submit();
                self.firmRegistrationForm.getLoader().start();
            }
        });
    }


    /**
     * @param firmData
     */
    private loadFirmInfoByTaxNumber(firmData:any){

        firmData.location = { 0: firmData.location.id, '_labels' : { 0: firmData.location.value } };

        this.firmRegistrationForm.populate( firmData , 'form[firm]');
    }
    
}