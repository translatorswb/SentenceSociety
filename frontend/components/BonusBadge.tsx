import * as React from 'react'

interface Props {
    points?: number;
}

const BonusBadge: React.FunctionComponent<Props> = ({points}) => {
    return (
        <div className='score-promise'>
            +<br />{points ? points : 2}
            </div>
    );
};

export default BonusBadge;