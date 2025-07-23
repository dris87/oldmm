import * as $ from 'jquery';
import { error } from 'util';
import { SystemConfig } from '../../libs/SystemConfig';
import 'bootstrap';
/**
 * This call will help to work with bootstrap modals...
 */
export class Modal{

    /**
     * In debug = true, we do not show loading
     * and console.log the data
     */
    public debug:boolean = false;

    /**
     * Any default ajax options here
     * @type {{}}
     */
    private options:ModalOptions = {};

    /**
     * 
     * @param $formElement
     * @param options 
     */
    public constructor( private $element:JQuery<HTMLElement>, options?:ModalOptions , showByDefault:boolean = false){

        this.debug = SystemConfig.debug;

        this.options = (<any>Object).assign( this.options , options );

        this.options.show = showByDefault;

        this.$element = $element.modal( this.options );

        this.$element.on('show.bs.modal', () => {
            this.setModalMaxHeight();
        });

        $(window).resize(() => {
            let modalIn = this.$element.find('.in');
            if (modalIn.length != 0) {
                this.setModalMaxHeight();
            }
        });

    }

    public getElement(){
        return this.$element;
    }

    /**
     * 
     */
    public show(){

        this.$element.modal('show');

    }

    public hide(){

        this.$element.modal('hide');

    }

    
    public toggle(){

        this.$element.modal('toggle');

    }

    /**
     * A function to be called if the request succeed
     * @param successCallback 
     */
    public addCloseEvent( callback: () => void ){
        this.$element.on('hide.bs.modal' , callback );
    }


    private setModalMaxHeight() {
        let $content        = this.$element.find('.modal-content');
        let borderWidth     = $content.outerHeight() - $content.innerHeight();
        let dialogMargin    = $(window).width() < 768 ? 20 : 60;
        let contentHeight   = $(window).height() - (dialogMargin + borderWidth);
        let headerHeight    = this.$element.find('.modal-header').outerHeight() || 0;
        let footerHeight    = this.$element.find('.modal-footer').outerHeight() || 0;
        let maxHeight       = contentHeight - (headerHeight + footerHeight);
    
        $content.css({
            'overflow': 'hidden'
        });
        
        this.$element.find('.modal-body').css({
            'max-height': maxHeight,
            'overflow-y': 'auto'
        });
    }


}