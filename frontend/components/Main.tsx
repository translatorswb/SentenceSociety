import * as React from "react";
import {MenuButton} from "./MenuButton";
import {Component} from "react";
import TranslationComponent from "./TranslationComponent";
import RegistrationComponent from "./RegistrationComponent";
import numberWithCommas from "../util/numberWithCommasFormatter";
import IApiService from "../service/IApiService";
import beforeUnloadWarning from "../util/beforeUnloadWarning";
import TermsScreen from "./TermsScreen";
//import BonusBadge from "./BonusBadge";
import {Statistics} from "./Statistics";
import {Share} from "./Share";
import randomFromArray from "../util/randomFromArray";
import {ProjectTarget} from "./ProjectTarget";
import {SentenceStats} from "./SentenceStats";
import {MembersStats} from "./MembersStats";
import ForgotPassword from "./ForgotPassword";
import VerifyEmailCheck from "./VerifyEmailCheck";
import Translate from "./Translate";

const POINTS_PER_ROUND = 2;

enum MainState {
    Home,
    Profile,
    About
}

enum SlideOverState {
    None,
    Translate,
    // Register
}

enum PopOverState {
    None,
    EndTranslationSession,
    Register,
    ForgotPassword = 3,
    VerifyEmailCheck = 4,
    Translate = 5,
}

export interface Props {
    // name: string;
    // enthusiasmLevel?: number;
    apiService: IApiService;
}

interface User {
    name: string;
}

interface Progress {
    translations: number;
    points: number;
}

interface HighscoreItem {
    name: string,
    points: number,
}

export interface ProfileData {
    name: string,
    points: number,
    verifiedEmail: boolean,
    pointsInTermText?: string,
    pointsInTermValue: number,
    highscores: HighscoreItem[],
    highscorePosition: number,
    rank: number,
    votes: {
        down: number,
        up1: number,
        up2: number,
        up3: number,
        up2ToNext: number,
        up3ToNext: number,
        downToNext: number,
    },
    translationsPending: number,
    translationsReviewed: number
}

interface State {
    task_step: number,
    slideOver: SlideOverState;
    popOver: PopOverState,
    // slideOver_showCloseDialog: boolean,
    // showRegisterFlow: boolean,
    main_state: MainState;
    progress: Progress;
    loginForm: {
        name: string;
        password: string;
        failureMessage: string;
        loginFailureMessage?: string;
    }
    profile?: ProfileData
    showTermsAndConditionsOverlay: boolean
    showBonus: boolean;
    loggedIn: boolean;
    loading: boolean;
    profileCardFlipped: boolean;

    translationChallengeText?: string;
    translationChallengeId?: number;

    verifyChallengeSourceText?: string;
    verifyChallengeTargetText?: string;
    verifyChallengedId?: number;

    statistics?: {
        time: string;
        facts: {
            type: string,
            text: string,
            value: number
        }[]
    }

    homeStatistics?: {
        totalSentences: number,
        verified: {
            time: string,
            up1: number,
            up2: number,
            up3: number,
        }
        members: {
            time: string,
            value: number
        }
    }
    quote: {
        text: string;
        by: string;
    };
}

const rankLookupTable = [
    'Beginner',
    'Junior',
    'Trainee',
    'Talent',
    'Intermediate',
    'Star',
    'Expert',
    'Master',
    'Mentor',
    'Champion',
    'Legend',
    'Wizard',
];

export function createProfileDataFromResponse(data:any):ProfileData {
    return {
        name: data.name,
        points: data.profile.points,
        highscores: data.highscores,
        highscorePosition: data.profile.highscorePosition,
        rank: data.profile.rank,
        pointsInTermValue: data.profile.pointsInTermValue,
        pointsInTermText: data.profile.pointsInTermText,
        votes: data.profile.votes,
        translationsPending: data.profile.translationsPending,
        translationsReviewed: data.profile.translationsReviewed,
        verifiedEmail: data.verifiedEmail
    }
}

class Main extends Component<Props, State> {
    // see https://github.com/Microsoft/TypeScript-React-Starter#stateful-components

    private apiService:IApiService;

    constructor(props: Props) {
        super(props);
        let quotes = [
            {text: "“Be part of a transformational global initiative and contribute to data collection in your language.”", by: " "},
            
        ];
        let quote:any = randomFromArray(quotes);
        this.state = {
            task_step: 0,
            slideOver: SlideOverState.None,
            popOver: PopOverState.None,
            main_state: MainState.Profile,
            progress: {translations: 0, points: 0},
            showTermsAndConditionsOverlay: false,
            showBonus: true,
            loggedIn: false,
            loading: false,
            profileCardFlipped: false,
            loginForm: {
                name: '',
                password: '',
                failureMessage: '',
            },
            quote,
        };
        this.apiService = props.apiService;
    }

    componentDidMount() {

        this.setState({loading: true});

        // beforeUnloadWarning(true);
        // this.setState({popOver: PopOverState.VerifyEmailCheck})

        this.apiService.checkLogin()
            .then((data) => {
                this.setState({
                    loggedIn: true,
                    loading: false,
                    popOver: data.verifiedEmail ? PopOverState.None : PopOverState.VerifyEmailCheck,
                    main_state: MainState.Profile,
                    profile: createProfileDataFromResponse(data)
                });
            })
            .catch((error) => {
                this.setState({
                    loading: false
                });
            });

        this.apiService.getHomeStats()
            .then((data) => {
                this.setState({homeStatistics: data});
            })
            .catch((error) => {
                console.log('getStats error', error);
            });
    }

    componentWillUpdate(nextProps:Props, nextState:State) {
        if (nextState.main_state !== this.state.main_state) {
            // console.log('change main_state');
        }
    }

    refreshTranslationChallenge(): Promise<void> {
        this.setState({loading: true});
        this.apiService.skipAndNextSourceAssignment(this.state.translationChallengeId)
            .then((data) => {
                this.setState({
                    translationChallengeText: data.text,
                    translationChallengeId: data.id,
                    loading: false
                });
            })
            .catch((error) => {
                this.setState({loading: false});
                console.log('getTranslationChallenge error', error);
            });
        return Promise.resolve();
    }

    refreshVerifyTranslationChallenge(): Promise<void> {
        this.setState({loading: true});
        this.apiService.skipAndNextVerifyAssignment(this.state.verifyChallengedId)
            .then((data) => {

                this.setState({
                    verifyChallengeSourceText: data.source,
                    verifyChallengeTargetText: data.text,
                    verifyChallengedId: data.id,
                    loading: false
                });
            })
            .catch((error) => {
                this.setState({loading: false});
            });
        return Promise.resolve();
    }

    submitTranslation(sourceId: number, text: string):Promise<void> {

        // update translationcount
        let progress = this.state.progress;
        progress.translations += 1;
        progress.points += 2;
        this.setState({progress});

        this.setState({loading: true});
        return this.apiService.submitTranslation(sourceId, text)
            .then((data) => {
                // console.log('submitTranslation data', data);
                this.setState({loading: false});
                return Promise.resolve();
                // this.setState({translationChallengeText: data.text, translationChallengeId: data.id, loading: false});
            })
            .catch((error) => {
                this.setState({loading: false});
                console.log('submitTranslation error', error);
                return Promise.reject()
            });
    }

    submitScore(sourceId: number, score: number):Promise<void> {
        this.setState({loading: true});
        return this.apiService.submitVerify(sourceId, score)
            .then((data) => {
                // console.log('submitVerify data', data);
                this.setState({loading: false});
                return Promise.resolve();
                // this.setState({translationChallengeText: data.text, translationChallengeId: data.id, loading: false});
            })
            .catch((error) => {
                this.setState({loading: false});
                console.log('submitTranslation error', error);
                // return Promise.reject()
                return Promise.resolve();  // TODO fail in final version
            });
    }

    next(): Promise<any> {
        let progress = this.state.progress;
        let showBonus = this.state.showBonus;
        let loading = this.state.loading;

        let newStep = this.state.task_step + 1;

        if (newStep === 1) {
            showBonus = true;
        } else if (newStep === 4) {
            // you've earned 4 points! (TODO in back-end later)
            progress.points += 2;

            showBonus = false;

            this.props.apiService.requestBonus().then((data) => {


            });
        } else if (newStep >= 5) {

            // request bonus, don't wait for it being awarded or not
           /* this.props.apiService.requestBonus().then((data) => {


            });*/

            // start over, but skip the intro screen
            newStep = 1;
            loading = true;
            this.apiService.getTranslationChallenge()
                .then((data) => {
                    this.setState({
                        translationChallengeText: data.text,
                        translationChallengeId: data.id,
                        loading: false,
                        task_step: newStep,
                        progress,
                        showBonus,
                        popOver: PopOverState.None,
                    });
                })
                .catch((error) => {
                    console.log('getTranslationChallenge error', error);
                });
        }

        if (newStep == 2 || newStep == 3) {
            // this.setState({
            //     verifyChallengeSourceText: "Another source text",
            //     verifyChallengeTargetText: "Another translated version",
            //     verifyChallengedId: 3247362876
            // });

            // this.setState({
            //     loading: true
            // });
            loading = true;
            this.apiService.getVerifyChallenge()
                .then((data) => {
                    // console.log('getVerifyChallenge data', data);
                    // this.setState({loading: false});

                    // verifyChallengeSourceText?: string;
                    // verifyChallengeTargetText?: string;
                    // verifyChallengedId?: number;

                    this.setState({
                        loading: false,
                        verifyChallengeSourceText: data.source,
                        verifyChallengeTargetText: data.text,
                        verifyChallengedId: data.id,
                        task_step: newStep,
                        progress,
                        showBonus,
                        popOver: PopOverState.None,
                    });
                })
                .catch((error) => {
                    console.log('getVerifyChallenge error', error);
                });
        }

        if (loading) {
            this.setState({loading})
        } else {
            this.setState({task_step: newStep, progress, showBonus, popOver: PopOverState.None, loading})
        }

        return Promise.resolve();
    }

    flagCurrent():Promise<void> {
        if (this.state.task_step === 1) {
            // flag source sentence - not possible in the front-end so no action at the moment
            return Promise.resolve();
        } else {
            this.setState({loading: true});
            return this.apiService.flagVerify(this.state.verifyChallengedId)
                .then((data: any) => {
                    this.setState({
                        verifyChallengeSourceText: data.source,
                        verifyChallengeTargetText: data.text,
                        verifyChallengedId: data.id,
                        loading: false,
                    });
                })
                .catch((error) => {
                    console.error('flag error', error);
                });
        }
    }

    startTranslating() {
        if (this.state.loggedIn){
            beforeUnloadWarning(true);
            this.apiService.getTranslationChallenge()
                .then((data) => {
                    this.setState({
                        translationChallengeText: data.text,
                        translationChallengeId: data.id,
                        loading: false
                    });
                })
                .catch((error) => {
                    console.log('getTranslationChallenge error', error);
                });
            this.setState({
                task_step: 0,
                slideOver: SlideOverState.Translate,
                showBonus: false,
                loading: true,
            });
        }else {
            this.setState({
                popOver: PopOverState.Translate
            });
            //return this.gotoMainState(MainState.Profile);
        }
    }
    get profileName():string {
        return this.state.profile ? this.state.profile.name : '--unknown--';
    }

    get rankName():string {

        const lookup = (idx:number):string => {
            if (idx >= 0 && idx < rankLookupTable.length) {
                return rankLookupTable[idx];
            } else {
                return '--unknown--';
            }
        }

        return this.state.profile ? lookup(this.state.profile.rank) : '--unknown--';
    }

    rankCheckMarks() {
        return <div className='rank-checkmarks'>
            {
                rankLookupTable.map((item, idx) => {
                    return <div className={'rank-checkmarks__mark' + ((rankLookupTable.length - 1 - idx) <= (this.state?.profile?.rank || 0) ? ' checked' : '')}></div>
                })
            }
        </div>;
    }

    get slideOverSubtitle() {
        switch (this.state.task_step) {
            case 0:
            case 1:
            case 4:
                return 'Translate';
            case 2:
            case 3:
                return 'Verify'
            default:
                return '???'
        }
    }

    gotoMainState(value: MainState) {
        beforeUnloadWarning(false);
        this.setState({main_state: value, slideOver: SlideOverState.None, showBonus: true, popOver: PopOverState.None})
    }

    renderTermsAndConditionsOverlay() {
        return this.state.showTermsAndConditionsOverlay ?
            <div className='terms-overlay'>
                <div className='terms-dialog'>
                    <div className='terms-header'>
                        <button style={{float: 'right', paddingLeft: 10, paddingRight: 10, paddingTop: 5, paddingBottom: 5, margin: 0}}
                                className='action-button-b secondary'
                                onClick={(e) => {
                                    this.setState({showTermsAndConditionsOverlay: false});
                                }}>CLOSE</button></div>
                    {TermsScreen()}
                </div>
            </div>
            :
            null
    }

    renderMainContent() {
        switch (this.state.main_state) {
            case MainState.Home:
                return (
                    <div className="content">
                        <h1>TWB Data Validation</h1>
                        <h2>Digitizing Kinyarwanda Together </h2>
                        <div className='content-body content-body-home'>
                            {
                                this.state.homeStatistics ?
                                    <div>
                                        <ProjectTarget translations={this.state.homeStatistics.totalSentences} />
                                        <div style={{marginTop: 20, display: 'flex', flexDirection: 'row', justifyContent: 'space-between'}}>
                                            <SentenceStats
                                                time={this.state.homeStatistics.verified.time}
                                                vote1={this.state.homeStatistics.verified.up1}
                                                vote2={this.state.homeStatistics.verified.up2}
                                                vote3={this.state.homeStatistics.verified.up3} />
                                            <MembersStats
                                                time={this.state.homeStatistics.members.time}
                                                amount={this.state.homeStatistics.members.value} />
                                        </div>
                                        <Share/>
                                    </div>
                                    :
                                    <div>
                                        <ProjectTarget translations={0} />
                                        <div style={{marginTop: 20, display: 'flex', flexDirection: 'row', justifyContent: 'space-between'}}>
                                            <SentenceStats
                                                time={'...'}
                                                vote1={0}
                                                vote2={0}
                                                vote3={0} />
                                            <MembersStats
                                                time={'...'}
                                                amount={0} />
                                        </div>
                                        <Share/>
                                    </div>
                            }

                        </div>
                        <div className="content-overlay">
                        </div>
                    </div>
                );
            case MainState.About:
                return (
                    <div className="content">
                        <h1>TWB Data Validation</h1>
                        <h2>About CLEAR Global</h2>
                        <div className={'content-body'}>
                            <h3>Let's digitize Kinyarwanda together</h3>
                            <p>
                            CLEAR Global is creating a Kinyarwanda and English machine translation solution for education and tourism use cases. Contribute to help bridge language barriers for Kinyarwanda speakers all over the world.
                            </p>

                            <h3>Your contribution is valuable</h3>
                            <p>
                            To train the use cases, we need as many sentences translated and validated as possible. 
                            </p>
                            <h3>Get in touch</h3>
                            <p>
                                
                                Send us an &nbsp;
                                <a target="_blank" href="mailto:translators@translatorswithoutborders.org">
                                    e-mail
                                    {/*<img className='link-icon' src={require('../assets/img/link-email.svg')} ></img>*/}
                                </a>
                                &nbsp; or check out the &nbsp;
                                <a target="_blank" href="https://www.translatorswithoutborders.org/">
                                    Translators without Borders website
                                    {/*<img className='link-icon' src={require('../assets/img/link-site.svg')} ></img>*/}
                                </a>
                            </p>
                            <div>
                                <Share/>
                            </div>
                        </div>
                        <div className="content-overlay">
                        </div>
                    </div>
                );
            case MainState.Profile:
                return (
                    this.state.loggedIn ?
                        <div className="content">
                            <h1>TWB Data Validation</h1>
                            <h2>{this.profileName}</h2>
                            <div className={'content-body'}>
                                <div className={'profile-card-container' + (this.state.profileCardFlipped ? ' flip' : '')}
                                     // onClick={() => {this.setState({profileCardFlipped: !this.state.profileCardFlipped})}}
                                >
                                    <div className="flipper">
                                        <div className="profile-card-front">
                                            {this.rankCheckMarks()}
                                            <h1 className='profile-card__title'>{this.rankName}</h1>
                                            <table>
                                                <tbody>
                                                <tr>
                                                    <td className='left-column'>{this.state.profile?.votes.up3}</td>
                                                    <td>
                                                        <img src={require('../assets/img/thumbs-up.svg').default}></img>
                                                        <img src={require('../assets/img/thumbs-up.svg').default}></img>
                                                        <img src={require('../assets/img/thumbs-up.svg').default}></img>
                                                        <br />
                                                        <span className='grey-text-small'>
                                                            {this.state.profile?.votes?.up3ToNext || 0 > 0 ?
                                                                `+${this.state.profile?.votes.up3ToNext} for one rank up!`
                                                                :
                                                                ""}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td className='left-column'>{this.state.profile?.votes.up2}</td>
                                                    <td>
                                                        <img src={require('../assets/img/thumbs-up.svg').default}></img>
                                                        <img src={require('../assets/img/thumbs-up.svg').default}></img>
                                                        <br />
                                                        <span className='grey-text-small'>
                                                            {this.state.profile?.votes?.up2ToNext || 0 > 0 ?
                                                                `+${this.state.profile?.votes.up2ToNext} for one rank up!`
                                                                :
                                                                ""}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td className='left-column'>{this.state.profile?.votes?.down}</td>
                                                    <td>
                                                        <img src={require('../assets/img/thumbs-up.svg').default} className='down'></img>
                                                        <br />
                                                        <span className='grey-text-small'>
                                                            {this.state.profile?.votes.downToNext || 0 > 0 ?
                                                                `+${this.state.profile?.votes.downToNext} for one rank down`
                                                                :
                                                                ""}
                                                        </span>
                                                    </td>
                                                </tr>
                                                {/*<tr>*/}
                                                {/*<td className='left-column'>{this.state.profile.votes.up1}</td>*/}
                                                {/*<td>*/}
                                                {/*<img src={require('../assets/img/thumbs-up.svg')}></img>*/}
                                                {/*</td>*/}
                                                {/*</tr>*/}
                                                <tr>
                                                    <td className='left-column smaller'>{this.state.profile?.translationsPending}</td>
                                                    <td>
                                                        Pending<br />
                                                        <span className='grey-text'>sentences</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td className='left-column smaller'>{this.state.profile?.translationsReviewed}</td>
                                                    <td>
                                                        Reviewed<br />
                                                        <span className='grey-text'>sentences</span>
                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                        <div className="profile-card-back">
                                            {this.rankCheckMarks()}
                                            <h1 className='profile-card__title'>{this.profileName}</h1>
                                            <div style={{color: '#a6a4b7', margin: '0 30px'}}>
                                                <br />
                                                This side is empty for now - later this side will enable you to edit parts of your profile.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className='profile-card-hint'>
                                    {/*<img src={require('../assets/img/rotate.svg')} style={{height: 14}}></img> click on card for profile*/}
                                </div>
                                <Share/>
                            </div>
                        </div>
                        :
                        <div className="content">
                            <h1>TWB Data Validation</h1>
                            
                            <div className={'content-body'}>
                                <h3>Recognition of your efforts</h3>
                                <p>
                                    Contribute by translating or validating sentences in Kinyarwanda, and  earn certificates, reference letters, or  monetary rewards when you reach  certain thresholds. Learn more here.
                        
                                </p>
                                
                                <h3>Join our Kinyarwanda TWB Community</h3>
                                <p>
                                Be part of our community of volunteer translators helping Kinyarwanda speakers all over the world get information and be heard. Register <a href="https://translatorswithoutborders.org/join-the-twb-community/">here</a>!
                                </p>
                                
                                <h3>Instructions</h3>
                                <p>
                                To start translating and validating sentences, please create and validate an account on this platform by clicking on "Register" in the bottom-right corner of this page. After validation you can login using your account name and password on the right side of this page all the times you come back.
                                </p>

                                <Share/>
                            </div>
                        </div>
                );
        }
    }

    renderSideBar() {
        if (this.state.main_state === MainState.Profile) {
            return (
                this.state.loggedIn ?
                    <div className='side-bar'>
                        {this.renderHelpTranslatingButtonWithBonus()}
                        <hr className='squiggly-white' />
                        <div className='high-scores'>
                            {
                                this.state.profile?.highscores?.map((item, idx) => {return (
                                    <div key={idx} className='top-score-item'>
                                        <div className='top-score-index'>{idx + 1}</div>
                                        <div className='top-score-name'>
                                            {item.name}
                                        </div>
                                        <div className='top-score-points'>
                                            {numberWithCommas(item.points)} points
                                        </div>
                                        <hr className='squiggly-purple' />
                                    </div>
                                )})
                            }
                            <div className='your-score-item'>
                                <div className='top-score-index'>{this.state.profile?.highscorePosition}</div>
                                <div className='top-score-name'>
                                    {this.profileName}
                                </div>
                                <div className='top-score-points'>
                                    {numberWithCommas(this.state.profile?.points || 0)} points
                                </div>
                            </div>
                        </div>
                        <hr className='squiggly-white' />
                        <div className='your-total-points'>
                            <h3>Your total points</h3>
                            <div className='points'>{numberWithCommas((this.state.profile?.points || 0) + (this.state.progress.points))}</div>
                        </div>
                        <table className='your-recent-points'>
                            <thead>
                            <tr><th>Last session</th><th>{this.state.profile?.pointsInTermText}</th></tr>
                            </thead>
                            <tbody>
                            <tr><td>+{numberWithCommas(this.state.progress.points)}</td><td>+{numberWithCommas(this.state.profile?.pointsInTermValue || 0)}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    :
                    <div className='side-bar'>
                        {this.renderHelpTranslatingButtonWithBonus()}
                        <hr className='squiggly-white' />
                        {this.renderLoginOrRegisterSideBar('Login', '')}
                    </div>
            )
        } else if (this.state.main_state === MainState.Home || this.state.main_state === MainState.About) {


            return <div className='side-bar side-bar-home'>
                {this.renderHelpTranslatingButtonWithBonus()}
                <hr className='squiggly-white' />
                <div className='side-bar__quote'>
                    {this.state.quote.text}
                </div>
                <div className='side-bar__signed'>- {this.state.quote.by}</div>
            </div>;
        } else {
            // not in use at the moment!
                return (
                    <div className='side-bar'>
                        {this.renderHelpTranslatingButtonWithBonus()}
                        <hr className='squiggly-white' />
                        {
                            this.state.statistics ?
                                <Statistics
                                    timeText={this.state.statistics.time}
                                    facts={this.state.statistics.facts}/>
                                :
                                <div></div>
                        }
                    </div>
            )
        }

    }

    renderHelpTranslatingButtonWithBonus() {
        return <div className='button-bonus-wrap'>
            <button className={'action-button-a help-translating'} onClick={(evt) => {this.startTranslating()}}>START TRANSLATING</button>
        </div>
    }

    renderLoginOrRegisterSideBar(loginTitle:string, loginSubtitle: string) {
        return <React.Fragment>
            <div className='login-box'>
                <form onSubmit={(e) => {this.handleLoginFormSubmit(); e.preventDefault()}}>
                    <h3>{loginTitle}</h3>
                    <div>{loginSubtitle}</div>
                    <div className='login-box-failure'>{this.state.loginForm.failureMessage} &nbsp;</div>
                    <input
                        autoFocus
                        type="text"
                        value={this.state.loginForm.name}
                        placeholder='Username'
                        onChange={e => this.handleLoginFormNameChange(e)}
                    />
                    <input
                        value={this.state.loginForm.password}
                        onChange={e => this.handleLoginFormCodeChange(e)}
                        placeholder='Password'
                        type="password"
                    />

                    <a type='button' href='#' className='btn-forgot-password' onClick={() => this.handleForgotPasswordClicked()}>
                        Forgot Password
                    </a>

                    <button
                        className='action-button-b'
                        disabled={!this.validateLoginForm()}
                        type="submit"
                    >
                        LOGIN
                    </button>
                </form>
            </div>
            <hr className='squiggly-white' />
            <div style={{padding: '16px 30px 0 30px', fontSize: 18}}>
            Don't have an account yet?<br/>
            Register here:
            </div>
            <div className='register-box'>
            <button className='action-button-b' onClick={() => this.handleRegisterClicked()}>
                REGISTER
            </button>
            </div>
        </React.Fragment>;
    }

    handleLoginFormSubmit() {
        this.setState({
            loading: true,
            loginForm: { ...this.state.loginForm, failureMessage: ''},
        });
        this.apiService.login(this.state.loginForm.name, this.state.loginForm.password)
            .then(data => {
                // console.log('data', data);

                if (data) {

                    this.setState(
                        {
                            loggedIn: true,
                            loading: false,
                            main_state: MainState.Profile,
                            popOver: data.verifiedEmail ? PopOverState.None : PopOverState.VerifyEmailCheck,
                            profile: createProfileDataFromResponse(data)
                        });
                }

        }).catch(() => {
            this.setState(
                {
                    loggedIn: false,
                    loading: false,
                    main_state: MainState.Profile,
                    loginForm: { ...this.state.loginForm, failureMessage: 'Account or Password incorrect'},
                });
        })
        // setTimeout(() => this.setState(
        //     {loggedIn: true, loading: false, main_state: MainState.Profile}),
        //     1000
        // );
    }
    handleLoginFormNameChange(e: React.FormEvent<HTMLInputElement>) {
        this.setState({
            loginForm: {...this.state.loginForm, name: e.currentTarget.value, loginFailureMessage: ''}
        });
    }
    handleLoginFormCodeChange(e: React.FormEvent<HTMLInputElement>) {
        this.setState({
            loginForm: {...this.state.loginForm, password: e.currentTarget.value, loginFailureMessage: ''}
        });
    }
    validateLoginForm() {
        return this.state.loginForm.name
            && this.state.loginForm.name != ""
            && this.state.loginForm.password
            && this.state.loginForm.password != "";
    }
    handleRegisterClicked() {
        beforeUnloadWarning(true);
        this.setState({popOver: PopOverState.Register})
    }

    handleForgotPasswordClicked() {
        beforeUnloadWarning(true);
        this.setState({popOver: PopOverState.ForgotPassword})
    }


    render() {
        let wrapClassName = '';
        if (this.state.slideOver == SlideOverState.None) {
            wrapClassName = 'state_home';
        } else {
            if (this.state.popOver === PopOverState.EndTranslationSession) {
                wrapClassName = 'state_slide_over state_slide_over_closing';
            } else {
                wrapClassName = 'state_slide_over';
            }
        }
        if (this.state.loggedIn) {
            wrapClassName += ' loggedin';
        }
        return (
            <div id="wrap" className={wrapClassName}>
                <div id="main">
                    <div className="leftmenu">
                        <div className='logo'></div>
                        
                        <MenuButton type="profile" active={this.state.main_state === MainState.Profile} clicked={() => this.gotoMainState(MainState.Profile)}></MenuButton>
                        <MenuButton type="about" active={this.state.main_state === MainState.About} clicked={() => this.gotoMainState(MainState.About)}></MenuButton>
                        {this.state.loggedIn ?
                            <button
                                style={{position: 'absolute', bottom: 5, left: 6}}
                                className='logout-button'
                                onClick={() => {
                                    this.setState({loading: true});
                                    this.apiService.logout().then(() => {
                                        this.setState({loggedIn: false, loading: false, profile: undefined, popOver: PopOverState.None})
                                    }).catch(e => {
                                        console.error(e)
                                    })
                                }}>LOGOUT</button>
                            :
                            ''
                        }
                    </div>
                    {this.renderMainContent()}
                    {this.renderSideBar()}
                </div>
                <div id="slide-over-panel">
                    {
                        this.state.slideOver == SlideOverState.Translate ?
                            <div className="translation">
                                {this.renderSlideOverHeader()}
                                {<TranslationComponent
                                    task_step={this.state.task_step}
                                    next_step={() => {this.next()}}
                                    closeFn={() => this.gotoMainState(this.state.main_state)}
                                    flagFn={() => this.flagCurrent()}
                                    translationChallengeSourceText={this.state.translationChallengeText || ''}
                                    verifyChallengeSourceText={this.state.verifyChallengeSourceText || ''}
                                    verifyChallengeTargetText={this.state.verifyChallengeTargetText || ''}

                                    refreshTranslationChallenge={() => this.refreshTranslationChallenge()}
                                    refreshVerifyTranslationChallenge={() => this.refreshVerifyTranslationChallenge()}
                                    submitTranslation={(translatedText) => this.submitTranslation(this.state.translationChallengeId || 0, translatedText)}
                                    submitVerification={(score) => this.submitScore(this.state.verifyChallengedId || 0, score)}
                                />}
                            </div>
                            :
                            <div className='register'>
                            </div>
                    }

                </div>
                {this.renderBeforeCloseAndSubscribeOverlay()}
                {this.renderTermsAndConditionsOverlay()}
                <div id='loading' style={{visibility: this.state.loading ? 'visible' : 'hidden'}}>
                    <div className="spinner-wrap">
                        <div className="spinner"></div>
                    </div>
                </div>
            </div>
        );
    }

    renderBeforeCloseAndSubscribeOverlay() {
        if (this.state.popOver === PopOverState.EndTranslationSession) {
            return (
                <div className='close-overlay'>
                    {
                        this.state.loggedIn ?
                            <div className='close-panel loggedin'>
                                <div className='close-panel__main'>
                                    <div className='translation-thanks'>
                                        Your points have been added to your account
                                    </div>
                                    <div className='close-panel__main_footer'>
                                        <button className='action-button-b secondary' onClick={() => {this.gotoMainState(this.state.main_state)}}
                                                style={{fontSize: 18}}>
                                            STOP
                                        </button>
                                        <div className='button-bonus-wrap'>
                                            <button className='action-button-a' onClick={() => {this.next()}}>CONTINUE</button>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            :
                            <div className='close-panel'>
                                <div className='close-panel__main'>
                                    <div className='translation-main'>
                                        <div className='translation-thanks'>
                                            <h1>Stop this translation round?</h1>
                                            {this.state.progress.translations > 0 ? <p>Congratulations for
                                                adding {this.state.progress.translations} translations!</p>
                                                :
                                                <p>You didn't add translations yet.</p>
                                            }
                                            <p>
                                                You have earned {this.state.progress.points} points.
                                            </p>
                                            {/*{this.state.progress.translations > 0 ? <p>*/}
                                                {/*Come back tomorrow for a +100 bonus.*/}
                                                {/*</p>*/}
                                                {/*:*/}
                                                {/*<p></p>*/}
                                            {/*}*/}
                                        </div>
                                    </div>
                                    <div className='close-panel__main_footer'>
                                        {/*<div className='stop-without-saving-box'>*/}
                                            <button className='action-button-b secondary' onClick={() => {this.gotoMainState(this.state.main_state)}}
                                                    style={{fontSize: 14, lineHeight: '20px'}}>
                                                STOP WITHOUT SAVING
                                            </button>
                                        {/*</div>*/}
                                        <div className='button-bonus-wrap'>
                                            <button className='action-button-a' onClick={() => {this.next()}}>CONTINUE</button>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div className='close-panel__side'>
                                    {this.renderLoginOrRegisterSideBar('Save your results!', 'Login to save your results.')}

                                </div>
                            </div>
                    }

                </div>
            )
        } else if (this.state.popOver === PopOverState.Register) {
            // register clicked on profile / main page
            return <div className='close-overlay'>
                <div className='close-panel'>
                    <RegistrationComponent
                        closeFn={() => {
                            if (this.state.slideOver === SlideOverState.Translate) {
                                // back to the end translation session screen
                                this.setState({popOver: PopOverState.EndTranslationSession})
                            } else {
                                // back to home or profile screen
                                this.gotoMainState(this.state.main_state)
                            }
                        }}
                        showTermsAndConditionsFn={() => {this.setState({showTermsAndConditionsOverlay: true})}}
                        loggedInFn={(profile:ProfileData) => {
                            this.setState({
                                loading: false,
                                loggedIn: true,
                                popOver: profile.verifiedEmail ? PopOverState.None : PopOverState.VerifyEmailCheck,
                                // main_state: MainState.Profile,
                                profile
                            })

                        }}
                        setLoadingStatusFn={(loading:boolean) => {
                            this.setState({
                                loading: loading,
                            })
                        }}
                        api={this.apiService}
                    />
                </div>
            </div>
        } else if (this.state.popOver === PopOverState.ForgotPassword) {
            // register clicked on profile / main page
            return <div className='close-overlay-forgot-password'>
                <div className='close-panel-forgot-password'>
                    <ForgotPassword
                        closeFn={() => {
                            this.gotoMainState(this.state.main_state)
                        }}

                        setLoadingStatusFn={(loading:boolean) => {
                            this.setState({
                                loading: loading,
                            })
                        }}
                        api={this.apiService}
                    />
                </div>
            </div>
        }else if (this.state.popOver === PopOverState.VerifyEmailCheck) {
            // register clicked on profile / main page
            // @ts-ignore
            return <div className='email-close-overlay'>
                <div className='close-overlay-translate'>
                    <div className='close-panel-translate'>
                        <VerifyEmailCheck />
                    </div>
                </div>
            </div>
        }else if (this.state.popOver === PopOverState.Translate) {
            // register clicked on profile / main page
            // @ts-ignore
            return <div className='close-overlay-translate'>
                <div className='close-panel-translate'>
                    <Translate
                        closeFn={() => {
                            this.gotoMainState(this.state.main_state)
                        }}
                        setLoadingStatusFn={(loading:boolean) => {
                            this.setState({
                                loading: loading,
                            })
                        }}
                        api={this.apiService}
                    />
                </div>
            </div>
        } else {
            return null;
        }
    }

    renderSlideOverHeader() {
        return (
            <div className='slide-over-header'>
                <button className='virtual-back-button' onClick={() => {
                    if (this.state.slideOver == SlideOverState.Translate) {
                        this.setState({popOver: PopOverState.EndTranslationSession});
                    } else {
                        this.gotoMainState(this.state.main_state);
                    }
                }}></button>
                <h1>TWB Data Validation</h1>
                <h2>{this.slideOverSubtitle}</h2>
                <div className='progress-score-indicator'>
                    <table>
                        <tbody>
                        <tr>
                            <td className='progress-score-indicator--translations'>{numberWithCommas(this.state.progress.translations)}</td>
                            <td className='progress-score-indicator--points'>{numberWithCommas(this.state.progress.points)}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th className='progress-score-indicator--translations'>TRANSLATIONS</th>
                            <th className='progress-score-indicator--points'>POINTS</th>
                        </tr>
                        </tfoot>
                    </table>
                    
                </div>
            </div>
        )
    }
}

export default Main;
