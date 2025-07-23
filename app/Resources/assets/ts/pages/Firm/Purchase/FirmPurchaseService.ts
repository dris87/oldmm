import * as $ from 'jquery';
import { PackageView } from '../../../components/organisms/PackageView';
import QuantityInput from '../../../components/atoms/QuantityInput';
import Switch from '../../../components/atoms/Switch';
import ThreeStateButton from '../../../components/ThreeStateButton';

export default class FirmPurchaseServices{

    public constructor(){

        let $packages:JQuery<HTMLElement> = $("#firm-purchase-service-packages");
        let $offerServices:JQuery<HTMLElement> = $("#offer_services"); 

        let packageView:PackageView = new PackageView( $packages );

        let $switchElement:JQuery<HTMLElement> = $(".firm-purchase-offer-service-status-checkbox");
        let statusSwitch = new ThreeStateButton( $switchElement );

        /*
        let sw:Switch = new Switch( $offerServices );
        sw.initOnSwitchRequest();
        */
    }
    
}