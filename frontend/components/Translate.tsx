import {Component} from "react";
import * as React from "react";
import {createProfileDataFromResponse, ProfileData} from "./Main";
import IApiService from "../service/IApiService";

interface Props {
    closeFn: () => void,
    setLoadingStatusFn: (loading:boolean) => void,
    api:IApiService
}


export default class Translate extends Component<Props> {

    renderMain() {
        return (
            <div className='register-main'>

                <div className='translate-wrapper'>
                    <div className={'translate-explained'}>You need to login first</div>
                    <button className='translate-btn' onClick={() => this.props.closeFn()}>Close</button>
                </div>
            </div>
        );
    }
    render() {
        return (<div className='register-body'>
            {this.renderMain()}
        </div>)
    }

}