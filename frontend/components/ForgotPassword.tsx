import {Component} from "react";
import * as React from "react";
import {createProfileDataFromResponse, ProfileData} from "./Main";
import IApiService from "../service/IApiService";

interface Props {
    closeFn: () => void,
    setLoadingStatusFn: (loading:boolean) => void,
    api:IApiService
}
interface State {
    error?: string;
    email: string,
}

export default class ForgotPassword extends Component<Props, State> {
    constructor(props:Props) {
        super(props);
        this.state = {
            email: '',
        }
    }

    handleChange(e: React.FormEvent<HTMLInputElement>) {
        const fieldValue = e.currentTarget.value;
        this.setState({email: fieldValue});
    }

    validateEmail(email: string){
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    renderMain() {
        return (
            <div className='register-main'>

                <div className={'register-title'}>Enter Your email address</div>
                <div className={'register-explained'}>Enter your email address to receive a link to reset your password.</div>
                <input className='register-email' value={this.state.email} type='email' name='email' placeholder='Email Address' onChange={e => this.handleChange(e)} />
                {this.state.error? <div className={'register-error'}>{this.state.error}</div> : ''}

                <div className='register-actions'>
                    <button className='action-button-a secondary button-register-stop' onClick={() => this.props.closeFn()}>Close</button>
                    <button className='action-button-a register-step-confirm'  onClick={() => this.next()}>Send</button>
                </div>
            </div>
        );
    }
    render() {
        return (<div className='register-body'>
            {this.renderMain()}
        </div>)
    }

    next() {
        let error: string = "";
        this.props.setLoadingStatusFn(true);
        this.props.api.forgotPassword(this.state.email)
            .then(data => {
                if (data) {
                    this.props.closeFn();
                } else {
                    error = "There is no user with this email address in our system. Please check and try again.";
                }
                this.setState({error});
                this.props.setLoadingStatusFn(false);
            })
            .catch(reason => {
                error = reason;
                this.props.setLoadingStatusFn(false);
            })
    }
}