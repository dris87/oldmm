import * as $ from 'jquery';
import 'jquery.scrollto';
import { Framework } from '../Framework';

/**
 * This will scroll to a specific element on page
 * @source https://github.com/flesler/jquery.scrollTo
 */
export default class ScrollTo{

    private defaultElement:JQuery<HTMLElement> = $( window );

    /**
     * Any default scroll to options
     * @type {{}}
     */
    private options:ScrollToOptions = {
        duration: 800
    };

    /**
     * This must be a scrollable element, to scroll the whole window use $(window).
     *
     * @param {JQuery<HTMLElement>} $toElement
     * @param {ScrollToOptions} options
     */
    public constructor( private $toElement:JQuery<HTMLElement> , options?:ScrollToOptions ){

        this.options = (<any>Object).assign( this.options , options );

        this.defaultElement.scrollTo( $toElement , this.options );
        
    }


    /**
     * This static method must be called once, from the main.ts
     * This will initialize all links on your page, to use scrollTo
     * @param classSelector 
     * @param options 
     */
    public static initializeAllLinks( classSelector:string = 'b-scroll-to', options?:ScrollToOptions ){

        $('.' + classSelector).on('click', function( e ){
            let to = $(this).data( 'scroll-to-id' ),
            $to = $('#' + to);
            if( $to.length == 0 ){
                Framework.throwError( 'ScrollTo error: #' + to + ' element to scroll to not found!' );
                return;
            }
            new ScrollTo( $to , options );
            e.preventDefault();
            e.stopPropagation();
        } );

    }

}