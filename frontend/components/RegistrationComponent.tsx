import * as React  from "react";
import {Component} from "react";
import IApiService from "../service/IApiService";
import {createProfileDataFromResponse, ProfileData} from "./Main";
import { CountryDropdown } from 'react-country-region-selector';

const MIN_CODE_LENGTH = 6;

interface Props {
    closeFn: () => void,
    showTermsAndConditionsFn: () => void,
    loggedInFn: (profile:ProfileData) => void,
    setLoadingStatusFn: (loading:boolean) => void,
    api:IApiService
}
interface State {
    step: number;
    //progress: 0|1|2
    error_username?: string;
    error_mail?: string;
    error?: string;
    showCode: boolean,
    showCodeTriggered: boolean,
    termsAndConditionsAgreed: boolean,
    username: string,
    personalName: string,
    secret: string,
    secretRepeat: string,
    email: string,
    country: string,
}

function obfuscate(text:string) {
    return '*'.repeat(text.length);
}

function randomsFrom(possible:string, amount:number) {
    var result = '';
    for (var i = 0; i < amount; i++) {
        result += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return result;
}
// thanks https://stackoverflow.com/a/3943985/31884
function shuffle (input:string) {
    var a = input.split(""),
        n = a.length;

    for(var i = n - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = a[i];
        a[i] = a[j];
        a[j] = tmp;
    }
    return a.join("");
}
// thanks https://stackoverflow.com/a/1349426/31884
function generateSecret() {
    var text = "";
    var possibleLetters = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghkmnpqrstuvwxyz";
    var possibleDigits = "23456789";
    var possibleSpecial = "#@*^+:";

    text += randomsFrom(possibleLetters, 4);
    text += randomsFrom(possibleDigits, 2);
    text += randomsFrom(possibleSpecial, 2);

    return shuffle(text);
}

export default class RegistrationComponent extends Component<Props, State> {
    constructor(props:Props) {
        super(props);
        console.log('RegistrationComponent.constructor()');
        this.state = {
            step: 0,
            //error: undefined,
            //progress: 0,
            showCode: false,
            showCodeTriggered: false,
            termsAndConditionsAgreed: false,
            username: '',
            secret: '',
            secretRepeat: '',
            personalName: '',
            email: '',
            country: '',
        }
    }
    termsAndConditionsChange(e: React.ChangeEvent<HTMLInputElement>) {
        this.setState({termsAndConditionsAgreed: e.target.checked})
    }
    handleChange(e: React.FormEvent<HTMLInputElement>) {
        const fieldValue = e.currentTarget.value;
        const fieldName = e.currentTarget.name;
        switch (fieldName) {
            case 'secret':
                this.setState({secret: fieldValue});
                break;
            case 'secretRepeat':
                this.setState({secretRepeat: fieldValue});
                break;
            case 'username':
                this.setState({username: fieldValue});
                break;
            case 'personalName':
                    this.setState({personalName: fieldValue});
                    break;
            case 'email':
                this.setState({email: fieldValue});
                break;
            case 'country':
                this.setState({country: fieldValue});
                break;
            default:
                console.error('unknown field:', fieldName);
        }
    }

    validateEmail(email: string){
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    register(error: string){
        this.props.api.register(this.state.username, this.state.secret,  this.state.email, this.state.personalName, this.state.country)
            .then((data:any) => {
                //
                // notify parent we've logged in
                // this is the same data structure we would get after getting the profile, or logging in
                //
                if (data?.error === true) {
                    console.log({ data });
                    this.setState({ error_mail: "Email in use" });
                } else {
                    this.props.loggedInFn(createProfileDataFromResponse(data));

                    this.setState({error, error_mail: undefined});
                    this.props.setLoadingStatusFn(false);
                }
            })
            .catch(reason => {
                error = "registration failed - please try again later";
                this.props.setLoadingStatusFn(false);
            })
    }
    codesMatch() {
        return this.state.secret === this.state.secretRepeat;
    }
    codeLengthSufficient() {
        return this.state.secret.length >= MIN_CODE_LENGTH;
    }
    codeFeedback() {
        if (!this.codeLengthSufficient()) {
            return "Password should be at least " + MIN_CODE_LENGTH + " characters."
        } else if (!this.codesMatch()) {
            return "Passwords do not match."
        }
        return ""
    }
    renderMain() {
        return (
            <div className='register-main'>
                <div className={'register-title'}>Your account</div>
                <div className={'register-explained'}>By registering, you can come back any time you like, and improve your level of expertise.</div>
                {this.state.error_username ? <div className={'register-error'}>{this.state.error_username}</div> : ''}
                <input placeholder='Your username' type="text" className='register-input' value={this.state.username} name='username' onChange={e => this.handleChange(e)} />

                <div className={'register-title'}>Your password</div>
                {this.state.error ? <div className={'register-error'}>{this.state.error}</div> : ''}
                <div className={'register-error'}>{this.codeFeedback()} &nbsp;</div>
                <div className={'password-wrapper'}>
                    <input placeholder='Type password here' type={this.state.showCode ? 'text' : 'password'} className='register-code' value={this.state.secret} name='secret' onChange={e => this.handleChange(e)} />
                    <input placeholder='Repeat password' type={this.state.showCode ? 'text' : 'password'} className='register-code-repeat' value={this.state.secretRepeat} name='secretRepeat' onChange={e => this.handleChange(e)} />
                </div>
                <button className={'action-button-a register-step-show-code'} onClick={() => this.setState({showCode: !this.state.showCode, showCodeTriggered: true})}>
                    <div className={"eye" + (this.state.showCode ? ' closed' : '')}>show</div>
                </button>

                <div className='password-wrapper'>
                    <div>
                        <div className={'register-title'}>Your name</div>
                        <input placeholder='Your Name' type="text" className='register-input' value={this.state.personalName} name='personalName' onChange={e => this.handleChange(e)} />
                    </div>
                    <div>
                        <div className={'register-title'}>Your email address</div>
                        {this.state.error_mail ? <div className={'register-error'}>{this.state.error_mail}</div> : ''}
                        <input className='register-email' value={this.state.email} type='email' name='email' placeholder='Email Address' onChange={e => this.handleChange(e)} />
                    </div>
                </div>

                <div className={'register-title'}>Select Country</div>
                <CountryDropdown classes={'country-dropdown'}
                                 value={this.state.country}
                                 onChange={(country) => this.setState({ country })}  />

                <div className={'register-terms'}>
                    <input type='checkbox'
                           checked={this.state.termsAndConditionsAgreed}
                           onChange={e => this.termsAndConditionsChange(e)}
                           style={{marginTop: 5}}
                    />
                    I agree to the &nbsp; <a  style={{textDecoration: 'underline', cursor: 'pointer'}} href="https://translatorswithoutborders.org/privacy-policy/" target="_blank">Privacy Policy</a> &nbsp; and the &nbsp; <a href="https://translatorswithoutborders.org/wp-content/uploads/2022/03/Plain-language-Code-of-Conduct-for-Translators.pdf" target="_blank">Terms & Conditions</a>
                </div>

                <div className='register-actions'>
                    <button className='action-button-a secondary button-register-stop' onClick={() => this.props.closeFn()}>STOP REGISTERING</button>
                    <button className='action-button-a register-step-confirm' disabled={!this.state.termsAndConditionsAgreed || this.state.username === '' || this.state.secret === ''} onClick={() => this.next()}>OK</button>
                </div>
            </div>
        );
    }
    render() {
        return (<div className='register-body'>
            {this.renderMain()}
        </div>)
    }
    reset() {
        /*let step = 0;*/
        let error = undefined;
        /*let progress:0|1|2|3 = 0;*/
        this.setState({error})
    }

    next() {
        let error_username: string = "";
        let error_mail: string = "";
        let error: string = "";

        this.props.setLoadingStatusFn(true);
        // check username
        this.props.api.checkUsernameAvailable(this.state.username)
            .then((available) => {
                console.log({ available });
                if (available) {
                    // let secret = generateSecret();
                    this.setState({error_username, error_mail: undefined});

                    if(this.state.secret && this.state.secretRepeat && (this.state.secret === this.state.secretRepeat) || this.state.email ){
                        if(this.state.email){
                            if(this.validateEmail(this.state.email)){
                                this.register(error);
                            }else{
                                error_mail = "Email Invalid!.";
                                this.setState({error_mail});
                            }
                        }else{
                            error_mail = "Enter a valid email!";
                            this.setState({error_mail});
                        }
                    }
                } else {
                    error_username = "The username entered is not available. Please try another.";
                    this.setState({error_username});
                }
                this.props.setLoadingStatusFn(false);
            })
            .catch((reason:any) => {
                error_username = reason;
                this.props.setLoadingStatusFn(false);
            })

        this.props.setLoadingStatusFn(true);

        /*this.props.setLoadingStatusFn(true);
        // add email?
        this.props.api.addEmail(this.state.email)
            .then(success => {
                if (success) {
                    //this.props.closeFn();
                } else {
                    error = "registration failed - please try again later";
                }
                this.setState({error});
                this.props.setLoadingStatusFn(false);
            })
            .catch(reason => {
                error = reason;
                this.props.setLoadingStatusFn(false);
            })
        this.props.setLoadingStatusFn(true);
        this.props.api.addCountry(this.state.country)
            .then(success => {
                if (success) {
                    this.props.closeFn();
                } else {
                    error = "registration failed - please try again later";
                }
                this.setState({error});
                this.props.setLoadingStatusFn(false);
            })
            .catch(reason => {
                error = reason;
                this.props.setLoadingStatusFn(false);
            })*/
    }
}
