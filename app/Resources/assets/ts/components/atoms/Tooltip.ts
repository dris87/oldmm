import * as $ from 'jquery';
import 'bootstrap';
/**
 * Simple tooltip initialization
 */
export default class Tooltip{

    public constructor( selector:string = '[data-toggle="tooltip"]', additionalOptions:TooltipOptions = {}){

        $( selector ).tooltip();
        
    }

}
