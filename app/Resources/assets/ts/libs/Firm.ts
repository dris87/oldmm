import * as $ from 'jquery';
import {Util} from "./util/Util";
import {Form} from "./util/Form";
import {Modal} from "../components/molecules/Modal";
import {Loader} from "../components/atoms/Loader";
import {Request} from "./util/Request";
import Notification from "../components/atoms/Notification";

export class Firm {


    public constructor( ){

    }

    public initCvUnlock($el:JQuery<HTMLElement>){
        let ModalInstance:Modal = new Modal( $("#firm-candidate-unlock-modal") , {
            show: false
        });

        let modalRequest:Request = new Request( {
            method: 'POST'
        } );

        let loader:Loader = new Loader($('body'));

        modalRequest.addSuccessEvent( ( data:any )=>{
            new Notification( {
                title: data.title,
                message: data.message
            });
            if( data.success == 1 ) {
                lastClicked.remove();
            }
        } );

        modalRequest.addCompleteEvent(()=>{
            loader.stop();
        });

        $("#firm-candidate-unlock-modal").on('click', '.firm-candidate-unlock-modal-unlock', function (e) {

            loader.start();
            modalRequest.submit();
            ModalInstance.hide();

            e.preventDefault();
            e.stopPropagation();

        });

        let lastClicked:JQuery<HTMLElement> = undefined;

        $el.on('click', '.firm-candidate-unlock', function (e) {

            lastClicked = $(this);
            modalRequest.setOptionValue('url', $(this).attr('data-action'));
            ModalInstance.show();

            e.preventDefault();
            e.stopPropagation();

        });

    }
}