import * as $ from 'jquery';
import 'bootstrap';
import 'cropperjs';
import {Cropper} from "../../libs/Cropper";

/**
 *
 */
export default class Upload{

    public constructor(){

        $('.cropper').each(function() {
            let el:any = $(this);
            new Cropper(el);
        });
    }

}
