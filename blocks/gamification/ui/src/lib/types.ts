export interface Level {
  level: number;
  gamificationrequired: number;
  description: string | null;
  name: string | null;
  badgeurl: string | null;
}

export interface LevelsInfo {
  count: number;
  levels: Level[];
  algo: {
    enabled: boolean;
    base: number;
    coef: number;
  };
}
