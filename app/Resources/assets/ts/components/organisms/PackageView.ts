import QuantityInput, {QuantityInputChangeType} from "../atoms/QuantityInput";
import * as numeral from 'numeral';
import * as $ from "jquery";
import {Framework} from "../../libs/Framework";
import FirmPurchaseCart from "../../pages/Firm/Purchase/FirmPurchaseCart";
import {Request} from "../../libs/util/Request";
import Notification from "../atoms/Notification";
import {Loader} from "../atoms/Loader";

/**
 *  This method will toggle the loading screen on a specific container
 */
export class PackageView{

    /**
     *
     * @param {JQuery<HTMLElement>} $packageViewElement
     */
    public constructor( private $packageViewElement:JQuery<HTMLElement> ){

        let navElements = this.$packageViewElement.find('.m-package-view--nav-item'),
            contents = $packageViewElement.find('.m-package-view--content-box');

        navElements.on('click', function(){

            let identifier = $(this).attr('id').replace('-nav-item',''),
            content = $packageViewElement.find('#' + identifier + '-content.m-package-view--content-box');

            navElements.removeClass('active');
            navElements.addClass('inactive');
            $(this).removeClass('inactive').addClass('active');
            contents.removeClass('active').addClass('inactive');
            content.removeClass('inactive').addClass('active');

        });

        $.each( contents, function(){
            let loader = new Loader($(this));
            let request = new Request( {
                method: 'POST'
            } );

            request.addSuccessEvent( ( data:any )=>{
                if( data.success == 1 ) {
                    if( typeof data.title != undefined && typeof data.message != undefined ) {
                        new Notification({
                            title: data.title,
                            message: data.message
                        });
                    }

                    // return action on success

                }else{
                    Framework.throwErrorModal();
                }
            } );
            request.addCompleteEvent( () => {
                loader.stop();
            } );
            let $footer = $(this).find('.m-package-view--footer');
            let quantityInput:QuantityInput = new QuantityInput(
                $footer,
                {
                    onSuccessCallback: (currentVal:number, type:QuantityInputChangeType ) => {
                        let $price = $(this).find('.m-package-view--footer-price span'),
                            price = numeral($price.data('price'));
                        $price.html(price.multiply(currentVal).format('0,0').replace(',', ' '));
                        let url = $footer.attr('data-action') + '/' + type;
                        loader.start();
                        request.setOptionValue('url', url);
                        request.submit();
                    }
                }
            );
        });


    }

}
