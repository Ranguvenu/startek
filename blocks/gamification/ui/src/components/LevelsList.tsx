import React from 'react';
import { useStrings } from '../lib/hooks';
import { getMinimumPointsForLevel } from '../lib/levels';
import { Level as LevelType } from '../lib/types';
import Input from './Input';
import Level from './Level';
import NumberInput from './NumberInput';
import Str from './Str';

const LevelsList: React.FC<{
    levels: LevelType[];
    algoEnabled: boolean;
    onDescChange: (level: LevelType, desc: string | null) => void;
    onNameChange: (level: LevelType, name: string | null) => void;
    onPointsChange: (level: LevelType, n: number | null) => void;
}> = ({ algoEnabled, levels, onDescChange, onNameChange, onPointsChange }) => {
    return (
        <div className="gamification-table gamification-w-full">
            <div className="gamification-table-row odd:gamification-bg-gray-100 gamification-font-bold">
                <div className="gamification-w-10 gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                    <Str id="level" />
                </div>
                <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                    <Str id="pointsrequired" />
                </div>
                <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                    <Str id="levelname" />
                </div>
                <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                    <Str id="leveldesc" />
                </div>
            </div>
            {levels.map((level) => {
                return (
                    <LevelRow
                        level={level}
                        minPoints={getMinimumPointsForLevel(levels, level)}
                        pointsEditable={level.level > 1 && !algoEnabled}
                        onDescChange={(nb) => onDescChange(level, nb)}
                        onNameChange={(nb) => onNameChange(level, nb)}
                        onPointsChange={(nb) => onPointsChange(level, nb)}
                    />
                );
            })}
        </div>
    );
};

const Field: React.FC<{ level: LevelType; label: string }> = ({ level, label, children }) => {
    return (
        <label className="gamification-m-0 gamification-font-normal gamification-w-full">
            <div className="gamification-sr-only">
                <Str id="levelx" a={level.level} /> {label}
            </div>
            {children}
        </label>
    );
};

const LevelRow: React.FC<{
    level: LevelType;
    minPoints: number;
    pointsEditable: boolean;
    onDescChange: (desc: string | null) => void;
    onNameChange: (name: string | null) => void;
    onPointsChange: (points: number | null) => void;
}> = ({ minPoints, level, pointsEditable, onPointsChange, onNameChange, onDescChange }) => {
    const getStr = useStrings(['noname', 'nodescription', 'pointsrequired', 'description', 'name']);
    return (
        <div className="gamification-table-row odd:gamification-bg-gray-100">
            <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                <Level level={level} small />
            </div>
            <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle gamification-break-all">
                <Field level={level} label={getStr('pointsrequired')}>
                    <div className="gamification-inline-block">
                        <NumberInput
                            min={minPoints}
                            onChange={onPointsChange}
                            value={level.gamificationrequired}
                            size={5}
                            disabled={!pointsEditable}
                            className="gamification-px-2 gamification-py-1 gamification-w-28"
                        />
                    </div>
                </Field>
            </div>
            <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                <Field level={level} label={getStr('name')}>
                    <Input
                        className="gamification-w-full"
                        placeholder={getStr('noname')}
                        onChange={(e) => onNameChange(e.target.value || null)}
                        defaultValue={level.name || ''}
                        maxLength={40}
                        type="text"
                    />
                </Field>
            </div>
            <div className="gamification-px-2 gamification-py-1 gamification-table-cell gamification-align-middle">
                <Field level={level} label={getStr('description')}>
                    <Input
                        className="gamification-w-full"
                        placeholder={getStr('nodescription')}
                        onChange={(e) => onDescChange(e.target.value || null)}
                        defaultValue={level.description || ''}
                        maxLength={255}
                        type="text"
                    />
                </Field>
            </div>
        </div>
    );
};

export default LevelsList;
