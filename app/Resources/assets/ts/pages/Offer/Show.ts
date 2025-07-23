import * as $ from 'jquery';
import StickToTop from '../../libs/behavioural/StickToTop';
import { Modal } from '../../components/molecules/Modal';
import { Form } from '../../libs/util/Form';
import { Request } from '../../libs/util/Request';
import Notification from '../../components/atoms/Notification';
import { Framework } from '../../libs/Framework';
import { Loader } from '../../components/atoms/Loader';
import { Util } from '../../libs/util/Util';

export default class OfferShow{

    public constructor(){
        
        new StickToTop( $(".l-sticky-top-line") , $(".l-offer-show") );

        let notLoggedModal = $('#offer-apply-not-logged-modal'),
            successModal = $('#offer-apply-success-modal'),
            loggedModal = $('#offer-apply-logged-modal'),
            offerApplyButton = $("#offer-show-apply");
    
        if( notLoggedModal.length == 1 ){
            let ModalInstance:Modal = new Modal( notLoggedModal , {
                show: false
            });
            let loginForm = new Form( notLoggedModal.find( '#login-form' ) , {
                dataType: 'json',
                contentType: 'application/json'
            }, true);
            loginForm.addSuccessEvent( ( data ) => { 

                if(data.success == 1 ) {
                    loginForm.getElement().find('fieldset .alert').remove();
                    location.reload();
                }
                
            });
            loginForm.addErrorEvent( ( jqXHR, textStatus:any, errorThrown ) => {
                if( textStatus.responseJSON != undefined ){
                    if( loginForm.getElement().find('fieldset .alert').length > 0 ){
                        loginForm.getElement().find('fieldset .alert').html(textStatus.responseJSON.error);
                    }else{
                        loginForm.getElement().find('fieldset').prepend('<div class="alert alert-danger">'+ textStatus.responseJSON.error +'</div>');
                    }
                }
            } );
    
            offerApplyButton.on('click', ( e )=>{
                e.preventDefault();
                e.stopPropagation();
                ModalInstance.show();
            } );
        }else if( loggedModal.length == 1 ){

            let ModalInstance:Modal = new Modal( loggedModal , {
                show: false
            });

            let request = new Request( {
                method: 'POST'
            } );

            request.addSuccessEvent( ( data:any )=>{
                ModalInstance.hide();
                if( data.success == 1 ) {
                    Util.disableElement($(this),false);
                    $("#offer-show-applied").show();
                    offerApplyButton.hide();
                    new Modal( successModal , {} , true);
                }else{
                    Framework.throwErrorModal();
                }
            } );

            loggedModal.on('click','#offer-apply-logged-modal--button', function(e){

                request.setOptionValue( 'url' , loggedModal.find('input[name="offer_apply_cv"]:checked').attr('data-action') );

                Util.disableElement($(this),true);
                request.submit();

                e.preventDefault();
                e.stopPropagation();
            });

            offerApplyButton.on('click', ( e ) => {
                e.preventDefault();
                e.stopPropagation();
                ModalInstance.show();
            } );

        }else{
            let ModalInstance:Modal = new Modal( successModal , {
                show: false
            });
            let request = new Request( {
                method: 'POST'
            } );
            offerApplyButton.on('click', function( e ){

                request.setOptionValue( 'url' , $(this).data('action') );
                
                request.addSuccessEvent( ( data:any )=>{
                    if( data.success == 1 ) {
                        ModalInstance.show();
                        Util.disableElement($(this),false);
                        $("#offer-show-applied").show();
                        offerApplyButton.hide();
                    }else{
                        Framework.throwErrorModal();
                    }
                } );
                Util.disableElement($(this),true);
                request.submit();

                e.preventDefault();
                e.stopPropagation();
            } );
        }
    }
}