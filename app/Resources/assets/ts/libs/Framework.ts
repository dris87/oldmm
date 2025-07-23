import * as $ from 'jquery';
import { SystemConfig } from './SystemConfig';
import { Modal } from '../components/molecules/Modal';


export class Framework{

    public static throwErrorModal(){
        new Modal( $('#' + SystemConfig.generalErrorModalId) , {}, true );
    }

    public static throwError( message:string ){

        console.error(message);

    }

}