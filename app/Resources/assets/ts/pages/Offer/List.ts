import * as $ from 'jquery';
import ExpandableChoice from '../../components/ExpandableChoice';

export default class OfferList{

    public constructor(){
    
        new ExpandableChoice( $(".choice-expand") );

    }
    
}