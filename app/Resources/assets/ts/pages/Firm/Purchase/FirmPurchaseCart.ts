import * as $ from 'jquery';
import { Steps } from '../../../components/organisms/Steps';
import { Form } from '../../../libs/util/Form';
import QuantityInput, {QuantityInputChangeType} from '../../../components/atoms/QuantityInput';
import { Loader } from '../../../components/atoms/Loader';
import { Request } from '../../../libs/util/Request';
import Notification from '../../../components/atoms/Notification';
import { Framework } from '../../../libs/Framework';
import * as numeral from "numeral";
import {Util} from "../../../libs/util/Util";

export default class FirmPurchaseCart{

     /**
     * Step number by its name ;)
     */
    private stepNumbers: any = {
        checkCart : 0,
        checkDetails : 1,
        payment : 2
    };

    /**
     * Holds the steps lib instance
     */
    private steps: Steps;

    /**
     * Loader instance
     */
    private loader: Loader;

    public constructor(){

        this.loader = new Loader($('#firm-purchase-cart-steps'));

        this.steps = new Steps(
            $('#firm-purchase-cart-steps'),
            {
                theme: 'default',
                useURLhash: false,
                transitionEffect: 'fade',
                toolbarSettings: {
                    toolbarPosition: 'none', // none, top, bottom, both
                    toolbarButtonPosition: '', // left, right
                    showNextButton: false, // show/hide a Next button
                    showPreviousButton: false, // show/hide a Previous button
                },
                anchorSettings: {
                    anchorClickable: true,
                    enableAllAnchors: false,
                    markDoneStep: true,
                    markAllPreviousStepsAsDone: true,
                    removeDoneStepOnNavigateBack: false,
                    enableAnchorOnDoneStep: true
                },
                autoAdjustHeight: true,
                selected: 0
            }
         );
        $("#firm-purchase-cart-next").on('click', ()=>{
            this.steps.next();
        });
        this.initCheckCartStep();
        this.initCheckDetailsStep();
        this.initPaymentStep();

    }

    /**
     * #1 step
     * Firm Colleague registration step
     */
    private initCheckCartStep(){
        let packageRows = $("#firm-purchase-cart-package-list tr"),
            removableRows = $(".firm-purchase-cart-removable tr"),
            self = this;

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
                if( data.removeCartId !== undefined ){
                    packageRows.parent().find('tr[data-cart-id="'+data.removeCartId+'"]').remove();
                    removableRows.parent().find('tr[data-cart-id="'+data.removeCartId+'"]').remove();
                        if( $("#firm-purchase-cart-offer_exaltation-list tbody tr").length == 0 ){
                            $("#firm-purchase-cart-offer_exaltation-list").remove();
                        }
                        if( $("#firm-purchase-cart-advance_filter-list tbody tr").length == 0 ){
                            $("#firm-purchase-cart-advance_filter-list").remove();
                        }

                    self.steps.fixSize();
                }
                if( data.summary.priceGross == '0' ){
                    location.reload();
                }

                FirmPurchaseCart.renderFinalSumPanel( data.summary.priceNet , data.summary.vat, data.summary.priceVat , data.summary.priceGross );
            }else{
                Framework.throwErrorModal();
            }
        } );
        request.addCompleteEvent( () => {
            self.loader.stop();
        } );

        $.each( packageRows, function(){
            let quantityInput:QuantityInput = new QuantityInput(
                $(this).find('.firm-purchase-cart-package-quantity'),
                {
                    onSuccessCallback: (currentVal:number, type:QuantityInputChangeType) => {

                        let url = $(this).find('.firm-purchase-cart-package-quantity').data('action') + '/' + type;
                        self.loader.start();
                        request.setOptionValue( 'url' , url );
                        request.submit();

                    }
                }
            );
        });

        removableRows.find('.firm-purchase-cart-remove').on('click', function() {
            let url = $(this).attr('data-action');
            self.loader.start();
            request.setOptionValue( 'url' , url );
            request.submit();
        });
    }

    /**
     * #2 step
     * Check details
    */
    private initCheckDetailsStep(){}
    /**
     * #3 step
     * Payments step
    */
    private initPaymentStep(){

        this.steps.addOnShowCallback( this.stepNumbers.payment , ()=>{
            $("#firm-purchase-cart-next").hide();
            $("#firm-purchase-cart-pay").show();
        });
        this.steps.addOnLeaveCallback( this.stepNumbers.payment, () => {
            $("#firm-purchase-cart-next").show();
            $("#firm-purchase-cart-pay").hide();
            this.steps.enableForceLeave();
        } , 'backward' );

        $("#firm-purchase-cart-pay").on('click',function () {
            let loader = new Loader($('body'));
            loader.start();
        });

    }

    /**
     * Places the prices to the appropriate span
     *
     * @param {number} priceNet
     * @param {number} vat
     * @param {number} priceVat
     * @param {number} priceGross
     */
    private static renderFinalSumPanel( priceNet:number, vat:number, priceVat:number, priceGross:number ){

        $("#firm-purchase-cart-price-netto").html( Util.getReadablePrice( priceNet ) );
        $("#firm-purchase-cart-price-tax-key").html( Util.getReadablePrice( vat ) );
        $("#firm-purchase-cart-price-tax").html( Util.getReadablePrice( priceVat ) );
        $("#firm-purchase-cart-price-brutto").html(Util.getReadablePrice( priceGross ) );

    }

}
