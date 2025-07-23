import * as $ from 'jquery';
import { Request } from '../../../libs/util/Request';
import { Framework } from '../../../libs/Framework';
import Notification from '../../../components/atoms/Notification';
import Switch from '../../../components/atoms/Switch';

export default class FirmOfferList{

    private $element:JQuery<HTMLElement>;

    public constructor(){

        this.$element = $("#firm-offer-list");

        let sw:Switch = new Switch( this.$element );
        sw.initOnSwitchRequest();
    }
    
}