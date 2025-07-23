import * as $ from 'jquery';
import { Framework } from '../../libs/Framework';
import { Util } from '../../libs/util/Util';
import * as numeral from 'numeral';

/**
 * This will scroll to a specific element on page
 * @source https://github.com/flesler/jquery.scrollTo
 */
export default class QuantityInput{

    private lastChangeType:QuantityInputChangeType;

    private disabled:boolean;

    public constructor(
        private $element:JQuery<HTMLElement>,
        private callbacks?:QuantityInputCallbacks
    ){

        let self = this;

        this.$element.find('.btn-number').on('click',function(e){
            e.preventDefault();

            let fieldName:any   = $(this).attr('data-field');
            let type:any        = $(this).attr('data-type');
            let input:any       = self.$element.find("input[name='"+fieldName+"']");
            let currentVal:any  = parseInt(input.val());
            if (!isNaN(currentVal)) {
                if(type == 'minus') {
                    self.lastChangeType = QuantityInputChangeType.decrease;
                    if(currentVal > input.attr('min')) {
                        input.val(currentVal - 1).change();
                    }
                    if(parseInt(input.val()) == input.attr('min')) {
                        Util.disableElement( $(this) , true );
                    }


                } else if(type == 'plus') {
                    self.lastChangeType = QuantityInputChangeType.increase;
                    if(currentVal < input.attr('max')) {
                        input.val(currentVal + 1).change();
                    }
                    if(parseInt(input.val()) == input.attr('max')) {
                        Util.disableElement( $(this) , true );
                    }


                }
            } else {
                input.val(1);
            }
        });
        this.$element.find('.input-number').focusin(function(){
            $(this).data('oldValue', $(this).val());
        });

        this.disabled = this.$element.find('.input-number').prop('disabled');

        this.$element.find('.input-number').change(function() {

            let minValue:any =  parseInt($(this).attr('min'));
            let maxValue:any =  parseInt($(this).attr('max'));
            let valueCurrent:any = parseInt($(this).val().toString());

            let name:string = $(this).attr('name');
            if(valueCurrent >= minValue) {
                $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
            } else {
                // alert('Sorry, the minimum value was reached');
                valueCurrent = 1;
                $(this).val(valueCurrent);
            }
            if(valueCurrent <= maxValue) {
                $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
            } else {
                //alert('Sorry, the maximum value was reached');
                valueCurrent = 1;
                $(this).val(valueCurrent);
            }
            if( typeof self.callbacks.onSuccessCallback == 'function' ){
                self.callbacks.onSuccessCallback( valueCurrent , self.lastChangeType );
            }

        });
        this.$element.find(".input-number").keydown(function (e) {
            // if disabled, dont do anything
            if( self.disabled ){
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

    }

}

export interface QuantityInputCallbacks{
    onSuccessCallback?: (currentVal:number, type:QuantityInputChangeType) => void,
    onPreChangeCallback ?: (currentVal:number, type:QuantityInputChangeType) => void
}

export enum QuantityInputChangeType {
    decrease = 'decrease',
    increase = 'increase'
}
