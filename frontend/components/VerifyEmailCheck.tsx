import {Component} from "react";
import * as React from "react";


export default class VerifyEmailCheck extends Component{

    render() {
        return (
            <div className='translate-wrapper'>
                <div className={'verify-email-title'}>Note:</div>
                <div className={'verify-email-explained'}>Please check your inbox and verify your email address using the link we sent you.</div>
        </div>)
    }
}

