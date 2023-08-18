import * as React from "react";

interface Props {
    vote: number;
    disabled?: boolean;
    onClick?: () => void;
    addClassName?: string;
}

const contentForVotes = (votes:number): React.ReactNode => {
    switch (votes) {
        case -1:
            return <div className="thumbs-down">down</div>;
        case 1:
            return <React.Fragment>
                <div className="thumbs-up">up</div>
            </React.Fragment>;
        case 2:
            return <React.Fragment>
                <div className="thumbs-up">up</div>
                <div className="thumbs-up">up</div>
            </React.Fragment>;
        case 3:
            return <React.Fragment>
                <div className="thumbs-up">up</div>
                <div className="thumbs-up">up</div>
                <div className="thumbs-up">up</div>
            </React.Fragment>;
        default:
            return null;
    }
}

export const ValidationButton = (props:Props) => {
    return <button
        disabled={props.disabled}
        className={'action-button-a translation-vote ' + props.addClassName}
        onClick={() => {if (props.onClick) {props.onClick()}}}
        style={{position: 'relative'}}>
        <div className='button-selected-helper'></div>
        { contentForVotes(props.vote) }
    </button>
}
