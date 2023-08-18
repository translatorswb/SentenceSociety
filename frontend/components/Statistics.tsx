import * as React from "react";
import {Component, Fragment} from "react";

interface Props {
    timeText: string,
    facts: {
        type: string,
        text: string,
        value: number
    }[]
}
interface State {
}

const translationsTigrinya = "ትርጉማት";

export class Statistics extends Component<Props, State> {
    renderFacts() {
        return <Fragment>
            {
                this.props.facts.map((fact, idx:number) => {
                    let style:React.CSSProperties = {};
                    let main = <div>{fact.text}</div>;
                    let additionalText = <span></span>;
                    if (fact.type === 'translated-sentences-goal') {
                        let percentage = (fact.value / 1000000) * 100;
                        style = {background: `linear-gradient(to top, #CBD9EB ${percentage}%, white ${percentage}%)`};
                        additionalText = <div style={{fontSize: 18}}>{percentage}%<br /> of our goal of 1,000,000 sentences</div>;
                        main = <span></span>
                    } else if (fact.type === 'current-translations') {
                        additionalText = <div style={{fontSize: 18}}>ትርጉማት</div>;
                    }
                    return <div key={idx} className={'stats-fact-block stats-' + fact.type} style={style}>
                        {main}
                        {additionalText}
                    </div>;
                })
            }
        </Fragment>
    }
    render() {
        return <div className='side-bar__stats'>

            <div className='stats-time-indication'>{this.props.timeText}</div>
            {this.renderFacts()}
        </div>
    }

}