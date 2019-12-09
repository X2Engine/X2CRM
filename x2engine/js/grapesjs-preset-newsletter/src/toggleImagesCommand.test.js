import toggleImagesCommand from './toggleImagesCommand';

describe('toggleImagesCommand test', () => {
    let grapesjsMock;
    let toggleImagesCommandResult;
    let mockedImageComponent;
    let mockedImagePlaceholderComponent;
    let mockedOtherComponent;
    let mockedComponents;
    let eachComponentsCallback;

    beforeEach(() => {
        mockedComponents = {
            each: jest.fn(),
        };

        mockedImageComponent = {
            get: jest.fn((property) => {
                switch (property) {
                case 'type':
                    return 'image';
                case 'src':
                    return 'mockedSrc';
                case 'src_bkp':
                    return 'mockedSrcBkp';
                case 'components':
                    return mockedComponents;
                default:
                    return undefined;
                }
            }),
            set: jest.fn(),
        };
        mockedImagePlaceholderComponent = {
            get: jest.fn((property) => {
                switch (property) {
                case 'type':
                    return 'image';
                case 'src':
                    return '##';
                case 'src_bkp':
                    return 'mockedSrcBkp';
                case 'components':
                    return mockedComponents;
                default:
                    return undefined;
                }
            }),
            set: jest.fn(),
        };
        mockedOtherComponent = {
            get: jest.fn((property) => {
                switch (property) {
                case 'type':
                    return 'mockedType';
                case 'components':
                    return mockedComponents;
                default:
                    return undefined;
                }
            }),
            set: jest.fn(),
        };

        grapesjsMock = {
            getComponents: jest.fn(() => mockedComponents),
        };

        toggleImagesCommandResult = toggleImagesCommand();
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    describe('on Run()', () => {
        beforeEach(() => {
            toggleImagesCommandResult.run(grapesjsMock);
            [[eachComponentsCallback]] = mockedComponents.each.mock.calls;
        });

        it('should call getComponents()', () => {
            expect(grapesjsMock.getComponents).toHaveBeenCalledTimes(1);
        });

        it('should iterate over components', () => {
            expect(mockedComponents.each).toHaveBeenCalledTimes(1);
            expect(eachComponentsCallback).toBeInstanceOf(Function);
        });

        describe('Components.each callback - image component', () => {
            beforeEach(() => {
                eachComponentsCallback(mockedImageComponent);
            });

            it('should set src as src_bkp', () => {
                expect(mockedImageComponent.set).toHaveBeenNthCalledWith(1, 'src_bkp', 'mockedSrc');
            });

            it('image src is set to "##"', () => {
                expect(mockedImageComponent.set).toHaveBeenNthCalledWith(2, 'src', '##');
            });

            it('should call components.each', () => {
                expect(mockedComponents.each).toHaveBeenCalledTimes(2);
            });
        });

        describe('Components.each callback - image component with src = ##', () => {
            beforeEach(() => {
                eachComponentsCallback(mockedImagePlaceholderComponent);
            });

            it('properties should not change', () => {
                expect(mockedImagePlaceholderComponent.set).not.toHaveBeenCalled();
            });

            it('should call components.each', () => {
                expect(mockedComponents.each).toHaveBeenCalledTimes(2);
            });
        });

        describe('Components.each callback - generic component', () => {
            beforeEach(() => {
                eachComponentsCallback(mockedOtherComponent);
            });

            it('properties should not change', () => {
                expect(mockedOtherComponent.set).not.toHaveBeenCalled();
            });

            it('should call components.each', () => {
                expect(mockedComponents.each).toHaveBeenCalledTimes(2);
            });
        });
    });

    describe('on Stop()', () => {
        beforeEach(() => {
            toggleImagesCommandResult.stop(grapesjsMock);
            [[eachComponentsCallback]] = mockedComponents.each.mock.calls;
        });

        it('should call getComponents()', () => {
            expect(grapesjsMock.getComponents).toHaveBeenCalledTimes(1);
        });

        describe('Components.each callback - image component', () => {
            beforeEach(() => {
                eachComponentsCallback(mockedImageComponent);
            });

            it('properties should not change', () => {
                expect(mockedImageComponent.set).not.toHaveBeenCalled();
            });
            it('should call components.each', () => {
                expect(mockedComponents.each).toHaveBeenCalledTimes(2);
            });
        });

        describe('Components.each callback - image component with src = ##', () => {
            beforeEach(() => {
                eachComponentsCallback(mockedImagePlaceholderComponent);
            });

            it('should set src as src_bkp', () => {
                expect(mockedImagePlaceholderComponent.set).toHaveBeenNthCalledWith(1, 'src', 'mockedSrcBkp');
            });

            it('should call components.each', () => {
                expect(mockedComponents.each).toHaveBeenCalledTimes(2);
            });
        });
    });
});
