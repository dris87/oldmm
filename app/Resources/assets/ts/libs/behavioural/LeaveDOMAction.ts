import * as $ from 'jquery';
import { Framework } from '../Framework';
import { Modal } from '../../components/molecules/Modal';

/**
 * NOTE: Unfinished implmentation
 * On leaving the html element with the cursor
 * This will fire any action you want
 */
export default class LeaveDOMAction{

    private menuElement:JQuery<HTMLElement> = $( window );

    public constructor( ){

        $("html").bind("mouseleave", function () {
            
            // show any modal, or do any action

            $("html").unbind("mouseleave");
        });
        
    }

}
