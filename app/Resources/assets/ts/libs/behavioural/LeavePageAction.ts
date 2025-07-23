import * as $ from 'jquery';
import { Framework } from '../Framework';
import { Modal } from '../../components/molecules/Modal';

/**
 * On leaving the page
 * This will fire an alert or prompt
 * to indicate it
 */
export default class LeavePageAction{

    private menuElement:JQuery<HTMLElement> = $( window );

    public constructor( ){

        // TODO: implement. jquery has removed the unload function, so have to find an alternative
        
    }

}
