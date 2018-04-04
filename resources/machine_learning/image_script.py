from PIL import Image
from numpy import array
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from random import randint
import os
import sys
import urllib

# Returns data about the file given
def getFileData(filePath):
		
		# Remote file, so save it temporarily
		if(filePath.startswith("http")):
			newFilename = os.path.basename(filePath);
			urllib.urlretrieve(filePath, 'data/temp/' + newFilename)
			filePath = 'data/temp/' + newFilename

		# convert image to 8-bit grayscale
		img = Image.open(filePath).convert('L')
		WIDTH, HEIGHT = img.size
		
		img = img.resize([400,400],Image.ANTIALIAS)
		data = list(img.getdata()) # convert image data to a list of integers
		# convert that to 2D list (list of lists of integers)
		data = [data[offset:offset+WIDTH] for offset in range(0, WIDTH*HEIGHT, WIDTH)]

		# At this point the image's pixels are all in memory and can be accessed
		# individually using data[row][col].
		os.remove(filePath)
		
		arr = array(img)

		imgValues = []
		for innerArrays in arr:
			for val in innerArrays:
				imgValues.append(val)

		#TODO: Something about this...
		if len(imgValues) == 5100:
			return

		return imgValues

def getDirectoryData(path):
	listing = os.listdir(path)
	directoryData = []
	for fileName in listing:
		# Skip hidden files
		if not fileName.startswith('.'):
			imgValues = getFileData(path + fileName)
			directoryData.append(imgValues)

	return directoryData

# Change the directory to this script's path
os.chdir(os.path.dirname(os.path.abspath(__file__)))

ImageToCheckPath = sys.argv[1]
dataPath = os.getcwd() + '/data/' + sys.argv[2]
imageToCheck = getFileData(ImageToCheckPath)
imageFileName = os.path.basename(ImageToCheckPath)
tempPath = os.getcwd() + "/data/temp/" + os.path.basename(ImageToCheckPath)
goodDestination = dataPath + "/good/" + imageFileName
badDestination = dataPath + "/bad/" + imageFileName
goodLabels = []
badLabels = []
size = 100, 100 # max size of image
goodTestValues = getDirectoryData(dataPath + '/good/')
badTestValues = getDirectoryData(dataPath + '/bad/')

# Setup the training data
i = 0
while i < len(goodTestValues):
	goodLabels.append(1)
	i+=1

i = 0
while i < len(badTestValues):
	badLabels.append(0)
	i+=1

features_train = array(goodTestValues + badTestValues)
labels_train = array(goodLabels + badLabels)

# initialize
clf = RandomForestClassifier()

# # train the classifier using the training data
clf.fit(features_train, labels_train)

# Make sure images have same num of values in their array
diff = abs(clf.n_features_ - len(imageToCheck))
# Make image arrays the same size to compare
if(diff > 0):
	for i in range(0, diff):
		if(len(imageToCheck) > clf.n_features_):
			imageToCheck.pop(i) # Remove the difference if image has more
		else:
			imageToCheck.append(0) # Add white (0) if image has less

predicted = clf.predict([imageToCheck]) # [1]

#move to folder that it predicted
#if(predicted == 1):
#	os.rename(tempPath, goodDestination)
#else:
#	os.rename(tempPath, badDestination)

print(predicted)