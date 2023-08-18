import * as React from "react";
import {Component} from "react";
import {IconButton} from "./IconButton";
import {StepsIndicator} from "./StepsIndicator";
//import BonusBadge from "./BonusBadge";
import {ValidationInstructions} from "./ValidationInstructions";
import {ValidationButton} from "./ValidationButton";

interface Props {
    task_step: number,
    next_step: () => void,
    closeFn: () => void,
    flagFn: () => void,
    refreshTranslationChallenge: () => Promise<void>,
    refreshVerifyTranslationChallenge: () => Promise<void>,
    submitTranslation: (translation: string) => Promise<void>,
    submitVerification: (score: number) => Promise<void>,
    translationChallengeSourceText: string
    verifyChallengeSourceText: string
    verifyChallengeTargetText: string
}
interface State {
    show_help_refresh: boolean;
    show_help_edit: boolean;
    show_help_flag: boolean;
    reallyFlag: boolean;
    showValidationHelp: boolean;
    score?: number;
    provideAlternativeTranslation: boolean;
    translationText?: string;
}

class TranslationComponent extends Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = {
            show_help_edit: false,
            show_help_refresh: false,
            show_help_flag: false,
            provideAlternativeTranslation: false,
            translationText: '',
            reallyFlag: false,
            showValidationHelp: false,
        }
    }
    next() {
        this.props.next_step();
        this.setState({score: undefined, reallyFlag: false});
    }
    submitTranslation() {
        this.props.submitTranslation(this.state.translationText || '').then(() => {
            this.props.next_step();
            this.setState({translationText: ''})
        });
    }
    submitAdditionalTranslation() {
        this.props.submitTranslation(this.state.translationText || '').then(() => {
            // this.props.next_step();
            // this.setState({translationText: ''})
            this.setState({provideAlternativeTranslation: false, translationText: ''});
        });
    }
    vote(score:number) {
        this.setState({score: score});
        this.props.submitVerification(score).then(() => {
            this.props.next_step();
            if (this.props.task_step < 4) {
                this.setState({score: undefined});
            }
        });
    }
    handleTranslationTextChange(e: React.FormEvent<HTMLTextAreaElement>) {
        this.setState({
            translationText: e.currentTarget.value
        });
    }

    showHelp(type: string) {
        switch (type) {
            case 'refresh':
                this.setState({'show_help_refresh': true});
            case 'edit':
                this.setState({'show_help_edit': true});
            case 'flag':
                this.setState({'show_help_flag': true});
            default:
                console.error('unexpected option ' + type)
        }
    }

    hideHelp() {
        this.setState({'show_help_refresh': false});
        this.setState({'show_help_edit': false});
        this.setState({'show_help_flag': false});
    }
    render() {
        switch (this.props.task_step) {
            case 0:
                return (
                    <div className={'translation-body'}>
                        <div className='translation-main'>

                            <h1>Rules of translation</h1>
                            <div className='translation-rules'>
                                There are two type of tasks in this exercise, translating and validating. When you validate a sentence you have 4 options:

                                <ValidationInstructions />

                                There are many options if you don't know the translation, just use the buttons to choose a new sentence or correct the sentence. Most of all, have fun!
                            </div>
                        </div>
                        <div className='slide-over-footer'>
                            <div>
                                {/*flex layout dummy*/}
                            </div>

                            <div className='button-bonus-wrap'>
                                <button className='action-button-a' onClick={() => {this.next()}}>START TRANSLATING</button>
                            </div>
                        </div>
                    </div>
                );
            case 1:
                return (
                    <div className={'translation-body'}>
                        <div className='translation-main'>
                            <div className={'translation-challenge'}>
                                {this.props.translationChallengeSourceText}
                                <IconButton type='refresh' clicked={() => {this.setState({translationText: ''}); this.props.refreshTranslationChallenge()}} help={<div>Get a different sentence</div>} />
                            </div>
                            <textarea className='translation-input'
                                placeholder='Add your translation here'
                                   value={this.state.translationText}
                                   onChange={e => this.handleTranslationTextChange(e)}
                                   autoComplete='off'
                                      rows={2}
                                      cols={120}
                            />
                            <div className={'translation-actions'}>
                                <button
                                    className={'action-button-a translation-confirm'}
                                    onClick={() => this.submitTranslation()}
                                    disabled={this.state.translationText === ''}
                                >SUBMIT</button>
                            </div>
                        </div>
                        <div className='slide-over-footer'>
                            <div>
                                {/*flex layout dummy*/}
                            </div>
                            <StepsIndicator mark={this.props.task_step} steps={3}/>
                        </div>
                    </div>
                );
            case 2:
            case 3:
            case 4:
                return (
                    <div className={'translation-body' + (this.props.task_step === 4 ? ' disabled' : '')}>
                        <div className='translation-main'>
                            <div className={'translation-challenge'}>{this.props.verifyChallengeSourceText}
                                <span className={'translation-buttons'}>
                                    <IconButton type='refresh'
                                                help={<div>Get a different sentence</div>}
                                                disabled={this.state.score !== undefined}
                                                clicked={() => {this.props.refreshVerifyTranslationChallenge()}}
                                    />
                                    <IconButton type='alternative-translation'
                                                help={<div>Add your own translation</div>}
                                                clicked={() => {
                                                    this.setState({provideAlternativeTranslation: true})
                                                }}
                                                disabled={this.state.score !== undefined}/>
                                    <IconButton type='flag'
                                                help={this.state.reallyFlag ?
                                                    <div style={{display: 'flex', justifyContent: 'center'}}>
                                                        <button className='button-flag-inappropriate'
                                                                onClick={() => {
                                                                    this.props.flagFn();
                                                                    setTimeout(() => {
                                                                        this.setState({reallyFlag: false})
                                                                    }, 200);
                                                                }}>REPORT AS<br/> OFFENSIVE
                                                        </button>
                                                    </div>
                                                    :
                                                    <div>Report content as offensive</div>}
                                                disabled={this.props.task_step === 4}
                                                keepOpen={this.state.reallyFlag}
                                                clickedOutside={() => this.setState({reallyFlag: false})}
                                                clicked={() => {this.setState({reallyFlag: true})}}
                                    />
                                </span>
                            </div>
                            <div className={'translation-translated'}>{this.props.verifyChallengeTargetText}</div>
                            <div className={'translation-actions'}>
                                <ValidationButton vote={-1} disabled={this.state.score !== undefined} onClick={() => this.vote(-1)} addClassName={this.state.score === -1 ? ' selected' : ''}/>
                                <ValidationButton vote={1} disabled={this.state.score !== undefined} onClick={() => this.vote(1)} addClassName={this.state.score === 1 ? ' selected' : ''}/>
                                <ValidationButton vote={2} disabled={this.state.score !== undefined} onClick={() => this.vote(2)} addClassName={this.state.score === 2 ? ' selected' : ''}/>
                                <ValidationButton vote={3} disabled={this.state.score !== undefined} onClick={() => this.vote(3)} addClassName={this.state.score === 3 ? ' selected' : ''}/>
                                <IconButton type='validation-help'
                                            help={<div><ValidationInstructions/></div>}
                                            keepOpen={this.state.showValidationHelp}
                                            clickedOutside={() => this.setState({showValidationHelp: false})}
                                            clicked={() => {this.setState({showValidationHelp: true})}} />
                            </div>
                        </div>
                        {this.state.provideAlternativeTranslation ?
                            <div className='translation-alternative-wrap'>
                                <div className='translation-alternative'>
                                    <div className={'translation-challenge'}>{this.props.verifyChallengeSourceText}</div>
                                    <div className={'translation-translated'}>{this.props.verifyChallengeTargetText}</div>

                                    <textarea className={'translation-alternative-input'}
                                              value={this.state.translationText}
                                              onChange={e => this.handleTranslationTextChange(e)}
                                              autoComplete='off'
                                    />
                                    <div className={'translation-actions'}>
                                        <button className='action-button-a secondary translation-confirm' onClick={() => {
                                            this.setState({provideAlternativeTranslation: false});
                                        }}>CANCEL</button>
                                        <button className={'action-button-a translation-confirm'}
                                                disabled={this.state.translationText === ''}
                                                onClick={() => {
                                            this.submitAdditionalTranslation();
                                        }}>ADD</button>
                                    </div>
                                </div>
                            </div>
                            :
                            null
                        }
                        {this.props.task_step < 4 ?
                            <div className='slide-over-footer'>
                                <div>
                                    {/*flex layout dummy*/}
                                </div>
                                <StepsIndicator mark={this.props.task_step} steps={3}/>
                            </div>
                            :
                            <div className='slide-over-footer'>
                                <div>
                                    {/*flex layout dummy*/}
                                </div>
                                <div className='button-bonus-wrap'>
                                    <button className='action-button-a' onClick={() => {this.next()}}>NEXT TRANSLATION</button>
                                   
                                </div>
                            </div>}
                    </div>
                )
            default:
                return (
                    <div className={'translation-body'}>Step {this.props.task_step}</div>
                )
        }

    }
}

export default TranslationComponent;
