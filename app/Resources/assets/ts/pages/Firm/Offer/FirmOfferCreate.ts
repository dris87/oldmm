import * as $ from 'jquery';
import { Form } from '../../../libs/util/Form';
import StickToTop from "../../../libs/behavioural/StickToTop";
import {Modal} from "../../../components/molecules/Modal";
import {Offer} from "../../../libs/Offer";

export default class FirmOfferCreate{

    public constructor(){

        let $offerCreateForm:JQuery<HTMLElement>    = $("#offer-manage-form"),
            offerCreateForm:Form                    = new Form( $offerCreateForm , {}, false, $('body')),
            previewModal:Modal                      = new Modal( $("#offer-preview-modal") );

        previewModal.getElement().find('#offer-preview-save-button').on('click', () =>{
            offerCreateForm.getElement().find('button[name="manage[save]"]').trigger('click');
            previewModal.hide();
        });
        previewModal.getElement().find('#offer-preview-submit-button').on('click', () =>{
            offerCreateForm.getElement().find('button[name="manage[submit]"]').trigger('click');
            previewModal.hide();
        });

        $('#offer-manage-preview').on('click', ()=>{
            Offer.formPreview( offerCreateForm , previewModal );
        });

        new StickToTop( $(".l-sticky-top-line") , $offerCreateForm );

    }
}