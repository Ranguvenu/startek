import React from 'react';
import { useString, useStrings } from '../lib/hooks';
import { getMinimumPointsForLevel } from '../lib/levels';
import { Level as LevelType } from '../lib/types';
import Input from './Input';
import Level from './Level';
import NumberInput from './NumberInput';
import Str from './Str';

const LevelsGrid: React.FC<{
    levels: LevelType[];
    algoEnabled: boolean;
    onDescChange: (level: LevelType, desc: string | null) => void;
    onNameChange: (level: LevelType, name: string | null) => void;
    onPointsChange: (level: LevelType, n: number | null) => void;
}> = ({ algoEnabled, levels, onDescChange, onNameChange, onPointsChange }) => {
    return (
        <div className="gamification-flex gamification-flex-wrap gamification--ml-4">
            {levels.map((level) => {
                return (
                    <LevelTile
                        key={level.level}
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
        <label className="gamification-m-0 gamification-font-normal">
            <div className="">
                <div className="gamification-sr-only">
                    <Str id="levelx" a={level.level} />
                </div>
                {label}
            </div>
            {children}
        </label>
    );
};

const LevelTile: React.FC<{
    level: LevelType;
    minPoints: number;
    pointsEditable: boolean;
    onDescChange: (desc: string | null) => void;
    onNameChange: (name: string | null) => void;
    onPointsChange: (points: number | null) => void;
}> = ({ minPoints, level, pointsEditable, onPointsChange, onNameChange, onDescChange }) => {
    const getStr = useStrings(['noname', 'nodescription', 'pointsrequired', 'description', 'name']);
    const levelx = useString('levelx', 'block_gamification', level.level);
    return (
        <div className="gamification-flex-none md:gamification-w-1/3 gamification-w-1/2 gamification-pl-4 gamification-mb-4">
            <div className="gamification-bg-gray-100 gamification-p-4 gamification-pt-2 gamification-rounded">
                <div className="gamification-flex gamification-flex-col gamification-items-center">
                    <div>
                        <div className="gamification-sr-only">{levelx}</div>
                        <Level level={level} />
                    </div>
                    <div className="gamification-w-full gamification-mb-2">
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
                    <div className="gamification-w-full gamification-mb-2 gamification-break-all">
                        <Field level={level} label={getStr('pointsrequired')}>
                            <NumberInput
                                min={minPoints}
                                onChange={onPointsChange}
                                value={level.gamificationrequired}
                                size={5}
                                disabled={!pointsEditable}
                                className="gamification-px-2 gamification-py-1 gamification-w-28"
                            />
                        </Field>
                    </div>
                    <div className="gamification-w-full">
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
            </div>
        </div>
    );
};

export default LevelsGrid;
