import * as $ from 'jquery';
import 'bootstrap';

/**
 * This will create an expandable choice list
 */
export default class StickToTop{

    /**
     *
     * @param {JQuery<HTMLElement>} $element
     * @param {JQuery<HTMLElement>} $botElement
     */
    public constructor( private $element:JQuery<HTMLElement> , private $botElement:JQuery<HTMLElement>){

        $(window).on('scroll',function(e){
            let $el = $element; 
            let $botEl = $botElement;
            let height = 70;
            let isPositionFixed = ($el.css('position') == 'fixed');
            if ($(this).scrollTop() > height && !isPositionFixed){
                $element.css({'position': 'fixed', 'top': '0px','width':'100%','margin-top':'0'});
                $botEl.css({'margin-top': height + 'px' });
            }
            if ($(this).scrollTop() < height && isPositionFixed)
            {
                $element.css({'position': 'static', 'top': '0px','width':'100%'}); 
                $botEl.css({'margin-top': '0px' });
            } 
        });
        $(window).trigger('scroll');
        
    }

}