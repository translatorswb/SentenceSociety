import * as React from "react";
import classNames from "classnames";

interface MenuButtonProps {
    type: string,
    clicked?: () => void,
    active: boolean,
    disabled?: boolean
}

export const MenuButton = (props:MenuButtonProps) =>
    <button
        className={classNames({['menubutton ' + props.type]: true, 'active': props.active, 'inactive': !props.active})} onClick={() => {props.clicked && props.clicked()}}>
        <div className='menubutton__marker'></div>
    </button>