declare module 'react-slick' {
    import { ReactNode } from 'react';
  
    export interface SlickSliderProps {
      dots?: boolean;
      infinite?: boolean;
      speed?: number;
      slidesToShow?: number;
      slidesToScroll?: number;
      arrows?: boolean;
      children?: ReactNode;
    }
  
    const SlickSlider: React.FC<SlickSliderProps>;
    export default SlickSlider;
  }
  