import { cx } from 'class-variance-authority';
import { Button } from './Button';
import { Label } from './Label';

export type FontSizeProps = {
  label?: string;
  name?: string;
  value: number;
  updateSize: (value: number) => void;
};

export const FontSize = ({ label, value, name, updateSize }: FontSizeProps) => {
  const minFontSize = 16;
  const maxFontSize = 24;

  const increaseFontSize = () => {
    const size = Number(Math.min(value + 1, maxFontSize));
    updateSize(size);
  };

  const decreaseFontSize = () => {
    const size = Number(Math.max(value - 1, minFontSize));
    updateSize(size);
  };

  return (
    <div className={cx('font-size-picker', label && 'has-label')}>
      {name ? <input type="hidden" name={name} value={Number(value)} /> : null}
      {label && <Label className="label">{label}</Label>}
      <div className="picker-container">
        <Button
          className="font-size-button"
          intent="invisible"
          aria-label="Increase font size"
          onClick={increaseFontSize}
        >
          A
        </Button>
        <Button
          className="font-size-button"
          intent="invisible"
          aria-label="Decrease font size"
          onClick={decreaseFontSize}
        >
          a
        </Button>
      </div>
    </div>
  );
};
